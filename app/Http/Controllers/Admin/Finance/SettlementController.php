<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class SettlementController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('access_finance_settlement') && !auth()->user()->hasPermission('access_finance')) {
            abort(403, 'Anda tidak memiliki akses ke Pelunasan Supplier.');
        }

        $suppliers = \App\Models\Supplier::select('id', 'name')->orderBy('name', 'ASC')->get();
        return view('admin.finance.settlement_report')->with([
            'sb' => 'FinanceSettlement',
            'suppliers' => $suppliers
        ]);
    }

    public function data(Request $request)
    {
        $query = $this->getFilteredQuery($request);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('product_name', function($row) {
                $merekName = trim($row->merek_name ?? '');
                $productName = trim($row->product_name ?? '');
                $variantName = trim($row->variant_name ?? '');

                $originalParts = array_filter([$merekName, $productName, $variantName]);
                $finalParts = [];
                foreach ($originalParts as $p1) {
                    $isSubPart = false;
                    foreach ($originalParts as $p2) {
                        if ($p1 !== $p2 && stripos($p2, $p1) !== false && strlen($p2) > strlen($p1)) {
                            $isSubPart = true;
                            break;
                        }
                    }
                    if (!$isSubPart) {
                        $finalParts[] = $p1;
                    }
                }
                $labelText = implode(' ', array_unique($finalParts));
                return '<div class="font-weight-600">' . $labelText . '</div>';
            })
            ->editColumn('buy_price', function ($row) {
                return 'Rp ' . number_format($row->buy_price, 0, ',', '.');
            })
            ->editColumn('total_cost', function ($row) {
                return 'Rp ' . number_format($row->total_cost, 0, ',', '.');
            })
            ->addColumn('raw_total_cost', function ($row) {
                return $row->total_cost;
            })
            ->addColumn('action', function($row) {
                return '<button type="button" class="btn btn-info btn-sm btn-detail" 
                            data-product-id="'.$row->product_id.'" 
                            data-variant-id="'.$row->product_variant_id.'"
                            data-buy-price="'.$row->buy_price.'"
                            data-product-name="'.e($row->product_name).'"
                            data-variant-name="'.e($row->variant_name).'">
                            <i class="fas fa-eye"></i> Detail
                        </button>';
            })
            ->rawColumns(['product_name', 'action'])
            ->with('summary', $this->getSummary($request))
            ->make(true);
    }

    private function getSummary(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        
        // Clone the query to calculate totals without grouping/ordering of the main table
        // Actually, since getFilteredQuery already has SUMs and GroupBy, we need to wrap it.
        $totals = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query)
            ->select(
                DB::raw('SUM(total_qty) as grand_total_qty'),
                DB::raw('SUM(total_cost) as grand_total_cost')
            )
            ->first();

        $supplier = null;
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $supplier = \App\Models\Supplier::find($request->supplier_id);
        }

        return [
            'total_qty' => $totals->grand_total_qty ?? 0,
            'total_cost' => $totals->grand_total_cost ?? 0,
            'supplier' => $supplier
        ];
    }

    private function getFilteredQuery(Request $request)
    {
        $query = DB::table('transaction_items')
            ->select(
                'transaction_items.product_id',
                'transaction_items.product_variant_id',
                DB::raw('COALESCE(NULLIF(transaction_items.buy_price, 0), product_variants.product_hpp, 0) as buy_price'),
                'merek.name as merek_name',
                'products.name as product_name',
                'suppliers.name as supplier_name',
                'product_variants.variant_name',
                'product_variants.sku_code',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.qty * COALESCE(NULLIF(transaction_items.buy_price, 0), product_variants.product_hpp, 0)) as total_cost')
            )
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('suppliers', 'products.supplier_id', '=', 'suppliers.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->leftJoin('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id');

        // Filter by date
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('transactions.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('transactions.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        // Filter by supplier
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $query->where('products.supplier_id', $request->supplier_id);
        }

        // Bundling Logic: Show components, hide bundle parents
        $query->where(function($q) {
            $q->where('products.is_bundle', 0)
              ->orWhereNotNull('transaction_items.parent_item_id');
        });

        // ONLY SHOW UNPAID ITEMS
        $query->whereNull('transaction_items.supplier_payment_id');

        return $query->groupBy(
            'transaction_items.product_id', 
            'transaction_items.product_variant_id', 
            DB::raw('COALESCE(NULLIF(transaction_items.buy_price, 0), product_variants.product_hpp, 0)'),
            'merek.name', 
            'products.name', 
            'suppliers.name',
            'product_variants.variant_name', 
            'product_variants.sku_code'
        )
        ->orderBy('total_qty', 'desc');
    }

    public function exportExcel(Request $request)
    {
        $items = $this->getFilteredQuery($request)->get();
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SettlementExport($items), 'Laporan-Pelunasan-Supplier-' . date('d-m-Y') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $items = $this->getFilteredQuery($request)->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.finance.settlement_pdf', compact('items'));
        return $pdf->download('Laporan-Pelunasan-Supplier-' . date('d-m-Y') . '.pdf');
    }

    public function paymentHistory()
    {
        if (!auth()->user()->hasPermission('access_finance_settlement') && !auth()->user()->hasPermission('access_finance')) {
            abort(403, 'Anda tidak memiliki akses ke Riwayat Pembayaran.');
        }

        $suppliers = \App\Models\Supplier::select('id', 'name')->orderBy('name', 'ASC')->get();
        return view('admin.finance.payment_history')->with([
            'sb' => 'FinanceSettlement',
            'suppliers' => $suppliers
        ]);
    }

    public function paymentHistoryData(Request $request)
    {
        if (!auth()->user()->hasPermission('access_finance_settlement') && !auth()->user()->hasPermission('access_finance')) {
            abort(403);
        }

        $query = DB::table('supplier_payments')
            ->select(
                'supplier_payments.id',
                'supplier_payments.payment_date',
                'supplier_payments.total_amount',
                'supplier_payments.payment_proof',
                'supplier_payments.notes',
                'suppliers.name as supplier_name',
                'users.name as cashier_name',
                DB::raw('(SELECT SUM(qty) FROM supplier_payment_items WHERE supplier_payment_id = supplier_payments.id) as total_qty')
            )
            ->leftJoin('suppliers', 'supplier_payments.supplier_id', '=', 'suppliers.id')
            ->leftJoin('users', 'supplier_payments.created_by', '=', 'users.id');

        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('supplier_payments.payment_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('supplier_payments.payment_date', '<=', $request->end_date);
        }
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $query->where('supplier_payments.supplier_id', $request->supplier_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('payment_date', function($row) {
                return Carbon::parse($row->payment_date)->format('d-m-Y');
            })
            ->editColumn('total_amount', function($row) {
                return 'Rp ' . number_format($row->total_amount, 0, ',', '.');
            })
            ->addColumn('payment_proof_link', function($row) {
                if ($row->payment_proof) {
                    return '<a href="'.asset('storage/'.$row->payment_proof).'" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-file-invoice"></i> Lihat</a>';
                }
                return '-';
            })
            ->addColumn('action', function($row) {
                return '<button type="button" class="btn btn-sm btn-primary btn-view-payment" data-id="'.$row->id.'"><i class="fas fa-eye"></i> Detail</button>';
            })
            ->rawColumns(['payment_proof_link', 'action'])
            ->make(true);
    }

    public function detail(Request $request)
    {
        $productId = $request->product_id;
        $variantId = $request->variant_id;
        $buyPrice = $request->buy_price;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $query = DB::table('transaction_items')
            ->select(
                'transactions.id',
                'transactions.transaction_code',
                'transactions.invoice_number',
                'transactions.created_at',
                'transaction_items.qty',
                DB::raw('COALESCE(NULLIF(transaction_items.buy_price, 0), product_variants.product_hpp, 0) as buy_price'),
                'transaction_items.price',
                'transaction_items.subtotal',
                'transactions.source',
                'users.name as cashier_name'
            )
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->leftJoin('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->where('transaction_items.product_id', $productId)
            ->whereNull('transaction_items.supplier_payment_id');

        if ($variantId && $variantId != 'null') {
            $query->where('transaction_items.product_variant_id', $variantId);
        } else {
            $query->whereNull('transaction_items.product_variant_id');
        }

        if ($buyPrice !== null && $buyPrice !== '' && $buyPrice !== 'null') {
            $query->where(DB::raw('COALESCE(NULLIF(transaction_items.buy_price, 0), product_variants.product_hpp, 0)'), $buyPrice);
        }

        if ($startDate) {
            $query->where('transactions.created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate) {
            $query->where('transactions.created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $sales = $query->orderBy('transactions.created_at', 'desc')->get();

        return response()->json([
            'sales' => $sales
        ]);
    }

    public function paymentDetail($id)
    {
        $payment = DB::table('supplier_payments')
            ->select(
                'supplier_payments.*',
                'suppliers.name as supplier_name',
                'users.name as cashier_name'
            )
            ->leftJoin('suppliers', 'supplier_payments.supplier_id', '=', 'suppliers.id')
            ->leftJoin('users', 'supplier_payments.created_by', '=', 'users.id')
            ->where('supplier_payments.id', $id)
            ->first();

        if (!$payment) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        $items = DB::table('supplier_payment_items')
            ->select(
                'supplier_payment_items.*',
                'products.name as product_name',
                'product_variants.variant_name',
                'merek.name as merek_name'
            )
            ->leftJoin('products', 'supplier_payment_items.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'supplier_payment_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->where('supplier_payment_items.supplier_payment_id', $id)
            ->get();

        return response()->json([
            'status' => 'success',
            'payment' => $payment,
            'items' => $items
        ]);
    }

    public function pay(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'payment_date' => 'required|date',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'products' => 'required|string', // JSON string from frontend
            'actual_payment_amount' => 'required|numeric',
        ], [
            'payment_proof.max' => 'Ukuran file bukti pembayaran tidak boleh lebih dari 2 MB.',
            'payment_proof.uploaded' => 'File bukti pembayaran gagal diunggah, kemungkinan besar karena ukuran file terlalu besar melebihi batas sistem.',
            'payment_proof.mimes' => 'Format file bukti pembayaran harus berupa JPG, JPEG, PNG, atau PDF.'
        ]);

        $products = json_decode($request->products, true);
        if (!$products || count($products) == 0) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada produk yang dipilih'], 400);
        }

        DB::beginTransaction();
        try {
            $paymentProofPath = null;
            if ($request->hasFile('payment_proof')) {
                $paymentProofPath = $request->file('payment_proof')->store('supplier_payments', 'public');
            }

            $paymentId = DB::table('supplier_payments')->insertGetId([
                'supplier_id' => $request->supplier_id,
                'payment_date' => $request->payment_date,
                'total_amount' => 0,
                'payment_proof' => $paymentProofPath,
                'notes' => $request->notes ?? null,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $totalAmount = 0;

            foreach ($products as $prod) {
                $productId = $prod['product_id'];
                $variantId = isset($prod['variant_id']) && $prod['variant_id'] != 'null' && $prod['variant_id'] != '' ? $prod['variant_id'] : null;

                $query = DB::table('transaction_items')
                    ->select(
                        'transaction_items.id',
                        'transaction_items.qty',
                        DB::raw('COALESCE(NULLIF(transaction_items.buy_price, 0), product_variants.product_hpp, 0) as buy_price'),
                        'transaction_items.product_variant_id'
                    )
                    ->leftJoin('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
                    ->where('transaction_items.product_id', $productId)
                    ->whereNull('transaction_items.supplier_payment_id');
                
                if ($variantId) {
                    $query->where('transaction_items.product_variant_id', $variantId);
                } else {
                    $query->whereNull('transaction_items.product_variant_id');
                }

                $items = $query->get();
                $qty = 0;
                $cost = 0;
                $buyPrice = 0;
                $idsToUpdate = [];

                foreach ($items as $item) {
                    $qty += $item->qty;
                    $cost += ($item->qty * $item->buy_price);
                    $buyPrice = $item->buy_price;
                    $idsToUpdate[] = $item->id;
                }

                if ($qty > 0) {
                    DB::table('supplier_payment_items')->insert([
                        'supplier_payment_id' => $paymentId,
                        'product_id' => $productId,
                        'product_variant_id' => $variantId,
                        'qty' => $qty,
                        'buy_price' => $buyPrice,
                        'subtotal' => $cost,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    DB::table('transaction_items')->whereIn('id', $idsToUpdate)->update([
                        'supplier_payment_id' => $paymentId
                    ]);

                    $totalAmount += $cost;

                    $this->deductPO($productId, $qty);
                }
            }

            DB::table('supplier_payments')->where('id', $paymentId)->update(['total_amount' => $request->actual_payment_amount]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Pembayaran berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function deductPO($productId, $qty)
    {
        $poItems = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->where('purchase_order_items.product_id', $productId)
            ->whereRaw('purchase_order_items.quantity > purchase_order_items.paid_qty')
            ->orderBy('purchase_orders.po_date', 'asc')
            ->select('purchase_order_items.*', 'purchase_orders.id as po_id')
            ->get();

        $remainingQty = $qty;

        foreach ($poItems as $poi) {
            if ($remainingQty <= 0) break;

            $unpaidQty = $poi->quantity - $poi->paid_qty;
            $payQty = min($unpaidQty, $remainingQty);

            DB::table('purchase_order_items')->where('id', $poi->id)->update([
                'paid_qty' => DB::raw('paid_qty + ' . $payQty)
            ]);

            $this->updatePOStatus($poi->po_id);

            $remainingQty -= $payQty;
        }
    }

    private function updatePOStatus($poId)
    {
        $items = DB::table('purchase_order_items')->where('purchase_order_id', $poId)->get();
        $totalQty = 0;
        $totalPaid = 0;
        foreach($items as $item) {
            $totalQty += $item->quantity;
            $totalPaid += $item->paid_qty;
        }

        $status = 'unpaid';
        if ($totalPaid >= $totalQty && $totalQty > 0) {
            $status = 'paid';
        } elseif ($totalPaid > 0) {
            $status = 'partial';
        }

        DB::table('purchase_orders')->where('id', $poId)->update([
            'payment_status' => $status
        ]);
    }
}
