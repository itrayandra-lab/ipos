<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Services\SupplierSettlementAllocationService;

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
            'suppliers' => $suppliers,
        ]);
    }

    public function data(Request $request)
    {
        try {
            $query = $this->getFilteredTransactionQuery($request);

            $summary = $this->getSummary($query, $request);

            \Log::debug('Settlement search params', $request->only(['start_date', 'end_date', 'supplier_id', 'search']));

            return DataTables::of($query)
                ->addIndexColumn()
                /*
                 * PRODUCT NAME: merek + nama produk + netto + satuan.
                 * Varian tidak ditampilkan terpisah karena isinya mengulang
                 * nama produk + ukuran.
                 */
                ->editColumn('product_name', function ($row) {
                    $merekName = trim((string) ($row->merek_name ?? ''));

                    $productName = trim((string) ($row->product_name ?? ''));

                    $nettoValue = trim((string) ($row->netto_value ?? ''));

                    $satuan = trim((string) ($row->satuan ?? ''));

                    $originalParts = array_filter([$merekName, $productName]);

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

                    $label = implode(' ', array_unique($finalParts));

                    $size = trim(implode(' ', array_filter([$nettoValue, $satuan])));

                    return trim($label . ' ' . $size);
                })

                /*
                 * TOTAL COST tetap numerik agar sorting benar; format Rupiah
                 * dilakukan di sisi tabel.
                 */
                ->with('summary', $summary)

                ->make(true);
        } catch (\Throwable $e) {
            \Log::error('Settlement data error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json(
                [
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Laporan utama sengaja tidak memakai alokasi PO. Alokasi PO baru dihitung
     * pada halaman berikutnya. Baris di sini menampilkan qty dan nilai transaksi
     * apa adanya, termasuk yang sudah lunas (ditandai badge dan tidak bisa dipilih).
     */
    private function getFilteredTransactionQuery(Request $request)
    {
        $paidQuantities = DB::table('supplier_payment_item_allocations')
            ->select('transaction_item_id')
            ->selectRaw('SUM(quantity) as paid_quantity')
            ->groupBy('transaction_item_id');

        $query = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->leftJoin('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('product_nettos', 'product_variants.product_netto_id', '=', 'product_nettos.id')
            ->leftJoin('product_batches', 'transaction_items.product_batch_id', '=', 'product_batches.id')
            ->leftJoin('suppliers as batch_suppliers', 'product_batches.supplier_id', '=', 'batch_suppliers.id')
            ->leftJoin('suppliers as product_suppliers', 'products.supplier_id', '=', 'product_suppliers.id')
            ->leftJoin('purchase_order_items as po_items', 'transaction_items.purchase_order_item_id', '=', 'po_items.id')
            ->leftJoin('purchase_orders as po', 'po_items.purchase_order_id', '=', 'po.id')
            ->leftJoin('goods_receipt_items', 'product_batches.goods_receipt_item_id', '=', 'goods_receipt_items.id')
            ->leftJoin('goods_receipts', 'goods_receipt_items.goods_receipt_id', '=', 'goods_receipts.id')
            ->leftJoin('purchase_orders as gr_po', 'goods_receipts.purchase_order_id', '=', 'gr_po.id')
            ->leftJoin('purchase_order_items as batch_po_items', 'product_batches.purchase_order_item_id', '=', 'batch_po_items.id')
            ->leftJoin('purchase_orders as batch_po', 'batch_po_items.purchase_order_id', '=', 'batch_po.id')
            ->leftJoin('purchase_order_items as receipt_po_items', 'goods_receipt_items.purchase_order_item_id', '=', 'receipt_po_items.id')
            ->leftJoin('purchase_orders as receipt_po', 'receipt_po_items.purchase_order_id', '=', 'receipt_po.id')
            ->leftJoinSub($paidQuantities, 'paid_allocations', function ($join) {
                $join->on('paid_allocations.transaction_item_id', '=', 'transaction_items.id');
            })
            ->where(function ($q) {
                $q->where('products.is_bundle', 0)
                    ->orWhereNotNull('transaction_items.parent_item_id');
            })
            ->select([
                'transaction_items.id as transaction_item_id',
                'transaction_items.supplier_payment_id',
                'transactions.transaction_code',
                'transactions.invoice_number',
                DB::raw('DATE(COALESCE(transactions.transaction_date, transactions.created_at)) as transaction_date'),
                'transaction_items.product_id',
                'transaction_items.product_variant_id',
                'products.name as product_name',
                'merek.name as merek_name',
                'product_variants.variant_name',
                'product_variants.sku_code',
                DB::raw('COALESCE(product_nettos.netto_value, (SELECT pn.netto_value FROM product_nettos pn WHERE pn.product_id = products.id ORDER BY pn.id LIMIT 1)) as netto_value'),
                DB::raw('COALESCE(product_nettos.satuan, (SELECT pn2.satuan FROM product_nettos pn2 WHERE pn2.product_id = products.id ORDER BY pn2.id LIMIT 1)) as satuan'),
                DB::raw('COALESCE(batch_suppliers.id, product_suppliers.id) as supplier_id'),
                DB::raw('COALESCE(batch_suppliers.name, product_suppliers.name) as supplier_name'),
                DB::raw('COALESCE(po.po_number, batch_po.po_number, receipt_po.po_number, gr_po.po_number) as po_number'),
                DB::raw('transaction_items.qty as total_qty'),
                DB::raw('COALESCE(paid_allocations.paid_quantity, 0) as paid_qty'),
                DB::raw('COALESCE(NULLIF(transaction_items.buy_price, 0), NULLIF(product_batches.buy_price, 0), product_variants.product_hpp, 0) as unit_hpp'),
                DB::raw('transaction_items.qty * COALESCE(NULLIF(transaction_items.buy_price, 0), NULLIF(product_batches.buy_price, 0), product_variants.product_hpp, 0) as total_cost'),
            ]);

        if ($request->filled('start_date')) {
            $query->whereRaw('COALESCE(transactions.transaction_date, transactions.created_at) >= ?', [$request->start_date . ' 00:00:00']);
        }

        if ($request->filled('end_date')) {
            $query->whereRaw('COALESCE(transactions.transaction_date, transactions.created_at) <= ?', [$request->end_date . ' 23:59:59']);
        }

        if ($request->filled('supplier_id')) {
            $query->whereRaw('COALESCE(batch_suppliers.id, product_suppliers.id) = ?', [$request->supplier_id]);
        }

        return $query
            ->orderByRaw('COALESCE(transactions.transaction_date, transactions.created_at) DESC')
            ->orderByDesc('transaction_items.id')
            ->get()
            ->map(function ($row) {
                $row->total_qty = (float) $row->total_qty;
                $row->total_cost = (float) $row->total_cost;
                $row->paid_qty = (float) $row->paid_qty;
                $row->unit_hpp = (float) $row->unit_hpp;
                $row->supplier_id = $row->supplier_id !== null ? (int) $row->supplier_id : null;

                $row->remaining_qty = max(0, $row->total_qty - $row->paid_qty);

                if ($row->supplier_payment_id !== null
                    || ($row->total_qty > 0 && $row->paid_qty + 0.0001 >= $row->total_qty)) {
                    $row->payment_status = 'lunas';
                } elseif ($row->paid_qty > 0.0001) {
                    $row->payment_status = 'sebagian';
                } else {
                    $row->payment_status = 'belum';
                }

                return $row;
            });
    }

    public function allocationPage(Request $request)
    {
        if (!auth()->user()->hasPermission('access_finance_settlement') && !auth()->user()->hasPermission('access_finance')) {
            abort(403, 'Anda tidak memiliki akses ke Pelunasan Supplier.');
        }

        $validated = $request->validate([
            'transaction_item_ids' => 'required|array|min:1',
            'transaction_item_ids.*' => 'required|integer|distinct|exists:transaction_items,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);
        $transactionItemIds = array_map('intval', $validated['transaction_item_ids']);
        $productIds = DB::table('transaction_items')
            ->whereIn('id', $transactionItemIds)
            ->pluck('product_id')
            ->unique()
            ->map(fn($id) => (int) $id)
            ->all();

        $allocations = app(SupplierSettlementAllocationService::class)
            ->allocations(false, $productIds)
            ->filter(function ($allocation) use ($request, $transactionItemIds) {
                if (!in_array((int) $allocation->transaction_item_id, $transactionItemIds, true)) {
                    return false;
                }
                if ($request->filled('start_date')
                    && Carbon::parse($allocation->transaction_date)->toDateString() < Carbon::parse($request->start_date)->toDateString()) {
                    return false;
                }
                if ($request->filled('end_date')
                    && Carbon::parse($allocation->transaction_date)->toDateString() > Carbon::parse($request->end_date)->toDateString()) {
                    return false;
                }
                return !$request->filled('supplier_id') || (string) $allocation->supplier_id === (string) $request->supplier_id;
            });

        $payableAllocations = $allocations->whereNotNull('purchase_order_item_id');
        if ($payableAllocations->isEmpty()) {
            return redirect()->route('admin.finance.settlement.index')
                ->with('error', 'Tidak ada transaksi terpilih yang dapat dialokasikan ke PO yang sudah menerima produk.');
        }

        $purchaseOrders = $payableAllocations
            ->groupBy('purchase_order_id')
            ->map(function ($poAllocations) {
                $first = $poAllocations->first();
                $products = $poAllocations
                    ->groupBy(fn($allocation) => implode('|', [
                        $allocation->purchase_order_item_id,
                        $allocation->product_id,
                        $allocation->product_variant_id ?? 'null',
                        number_format((float) $allocation->buy_price, 4, '.', ''),
                    ]))
                    ->map(function ($items) {
                        $item = $items->first();
                        return (object) [
                            'purchase_order_id' => $item->purchase_order_id,
                            'po_number' => $item->po_number,
                            'supplier_id' => $item->supplier_id,
                            'supplier_name' => $item->supplier_name,
                            'product_name' => $item->product_name,
                            'merek_name' => $item->merek_name,
                            'variant_name' => $item->variant_name,
                            'sku_code' => $item->sku_code,
                            'transaction_item_ids' => $items->pluck('transaction_item_id')->unique()->values()->all(),
                            'qty' => (float) $items->sum('allocated_qty'),
                            'buy_price' => (float) $item->buy_price,
                            'subtotal' => (float) $items->sum('total_hpp'),
                        ];
                    })
                    ->values();

                return (object) [
                    'purchase_order_id' => $first->purchase_order_id,
                    'po_number' => $first->po_number,
                    'supplier_id' => $first->supplier_id,
                    'supplier_name' => $first->supplier_name,
                    'products' => $products,
                    'transaction_item_ids' => $poAllocations->pluck('transaction_item_id')->unique()->values()->all(),
                    'total_qty' => (float) $poAllocations->sum('allocated_qty'),
                    'total_amount' => (float) $poAllocations->sum('total_hpp'),
                ];
            })
            ->values();

        return view('admin.finance.settlement_allocations', [
            'purchaseOrders' => $purchaseOrders,
            'unmatchedQty' => (float) $allocations->whereNull('purchase_order_item_id')->sum('allocated_qty'),
            'selectedCount' => count($transactionItemIds),
            'totalAmount' => (float) $purchaseOrders->sum('total_amount'),
            'filters' => $request->only(['start_date', 'end_date', 'supplier_id']),
            'sb' => 'FinanceSettlement',
        ]);
    }

    private function getSummary($items, Request $request)
    {
        $supplier = null;
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $supplier = \App\Models\Supplier::find($request->supplier_id);
        }

        return [
            'total_qty' => (float) $items->sum('total_qty'),
            'total_cost' => (float) $items->sum('total_cost'),
            'supplier' => $supplier,
        ];
    }

    private function getFilteredQuery(Request $request)
    {
        // Laporan utama bersifat level transaksi (tanpa No. PO). Alokasi PO
        // ditampilkan pada halaman terpisah setelah transaksi dipilih.
        return $this->getFilteredTransactionQuery($request);
    }

    public function exportExcel(Request $request)
    {
        $items = $this->getFilteredQuery($request);
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SettlementExport($items), 'Laporan-Pelunasan-Supplier-' . date('d-m-Y') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $items = $this->getFilteredQuery($request);
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
            'suppliers' => $suppliers,
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
                'supplier_payments.payment_number',
                'supplier_payments.payment_date',
                'supplier_payments.total_amount',
                'supplier_payments.payment_proof',
                'supplier_payments.notes',
                DB::raw("COALESCE(purchase_orders.po_number, (SELECT GROUP_CONCAT(DISTINCT linked_orders.po_number) FROM supplier_payment_items linked_items JOIN purchase_order_items linked_order_items ON linked_order_items.id = linked_items.purchase_order_item_id JOIN purchase_orders linked_orders ON linked_orders.id = linked_order_items.purchase_order_id WHERE linked_items.supplier_payment_id = supplier_payments.id)) as po_number"),
                'suppliers.name as supplier_name',
                'users.name as cashier_name',
            )
            ->leftJoin('suppliers', 'supplier_payments.supplier_id', '=', 'suppliers.id')
            ->leftJoin('purchase_orders', 'supplier_payments.purchase_order_id', '=', 'purchase_orders.id')
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
            ->editColumn('payment_date', function ($row) {
                return Carbon::parse($row->payment_date)->format('d-m-Y');
            })
            ->editColumn('total_amount', function ($row) {
                return 'Rp ' . number_format($row->total_amount, 0, ',', '.');
            })
            ->addColumn('payment_proof_link', function ($row) {
                if ($row->payment_proof) {
                    return '<a href="' . asset('storage/' . $row->payment_proof) . '" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-file-invoice"></i> Lihat</a>';
                }
                return '-';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-sm btn-primary btn-view-payment" data-id="' . $row->id . '"><i class="fas fa-eye"></i> Detail</button>';
            })
            ->rawColumns(['payment_proof_link', 'action'])
            ->make(true);
    }

    public function detail(Request $request)
    {
        $variantId = $request->input('variant_id');
        $purchaseOrderItemId = (int) $request->input('purchase_order_item_id');
        $allocations = app(SupplierSettlementAllocationService::class)
            ->allocations()
            ->filter(function ($allocation) use ($request, $variantId, $purchaseOrderItemId) {
                if ((int) $allocation->purchase_order_item_id !== $purchaseOrderItemId
                    || (int) $allocation->product_id !== (int) $request->product_id
                    || (string) ($allocation->product_variant_id ?? '') !== (string) ($variantId ?? '')) {
                    return false;
                }
                if ($request->filled('buy_price') && abs((float) $allocation->buy_price - (float) $request->buy_price) > 0.0001) {
                    return false;
                }
                if ($request->filled('start_date')
                    && Carbon::parse($allocation->transaction_date)->toDateString() < Carbon::parse($request->start_date)->toDateString()) {
                    return false;
                }
                return !$request->filled('end_date')
                    || Carbon::parse($allocation->transaction_date)->toDateString() <= Carbon::parse($request->end_date)->toDateString();
            })
            ->sortByDesc('transaction_date')
            ->values()
            ->map(function ($allocation) {
                return (object) [
                    'id' => $allocation->transaction_item_id,
                    'transaction_code' => $allocation->transaction_code,
                    'invoice_number' => $allocation->invoice_number,
                    'transaction_date' => $allocation->transaction_date,
                    'qty' => $allocation->allocated_qty,
                    'buy_price' => $allocation->buy_price,
                    'total_hpp' => $allocation->total_hpp,
                ];
            });

        $payments = DB::table('supplier_payment_item_allocations as allocations')
            ->join('supplier_payment_items as payment_items', 'allocations.supplier_payment_item_id', '=', 'payment_items.id')
            ->join('supplier_payments as payments', 'payment_items.supplier_payment_id', '=', 'payments.id')
            ->where('payment_items.purchase_order_item_id', $purchaseOrderItemId)
            ->where('payment_items.product_id', $request->product_id)
            ->when($variantId && $variantId !== 'null',
                fn($query) => $query->where('payment_items.product_variant_id', $variantId),
                fn($query) => $query->whereNull('payment_items.product_variant_id'))
            ->when($request->filled('buy_price'), fn($query) => $query->where('payment_items.buy_price', $request->buy_price))
            ->when($request->filled('start_date'), fn($query) => $query->whereDate('payments.payment_date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn($query) => $query->whereDate('payments.payment_date', '<=', $request->end_date))
            ->select([
                'payments.id',
                'payments.payment_date',
                'payments.payment_proof',
                'payment_items.buy_price',
            ])
            ->selectRaw('SUM(allocations.quantity) as qty')
            ->selectRaw('SUM(allocations.quantity * payment_items.buy_price) as subtotal')
            ->groupBy('payments.id', 'payments.payment_date', 'payments.payment_proof', 'payment_items.buy_price')
            ->orderByDesc('payments.payment_date')
            ->get();

        return response()->json([
            'sales' => $allocations,
            'payments' => $payments,
        ]);
    }

    public function paymentDetail($id)
    {
        $payment = DB::table('supplier_payments')
            ->select(
                'supplier_payments.*',
                'suppliers.name as supplier_name',
                'users.name as cashier_name',
                DB::raw("COALESCE(purchase_orders.po_number, (SELECT GROUP_CONCAT(DISTINCT linked_orders.po_number) FROM supplier_payment_items linked_items JOIN purchase_order_items linked_order_items ON linked_order_items.id = linked_items.purchase_order_item_id JOIN purchase_orders linked_orders ON linked_orders.id = linked_order_items.purchase_order_id WHERE linked_items.supplier_payment_id = supplier_payments.id)) as po_number")
            )
            ->leftJoin('suppliers', 'supplier_payments.supplier_id', '=', 'suppliers.id')
            ->leftJoin('users', 'supplier_payments.created_by', '=', 'users.id')
            ->leftJoin('purchase_orders', 'supplier_payments.purchase_order_id', '=', 'purchase_orders.id')
            ->where('supplier_payments.id', $id)
            ->first();

        if (!$payment) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        $items = DB::table('supplier_payment_items')
            ->select('supplier_payment_items.*', 'products.name as product_name', 'product_variants.variant_name', 'merek.name as merek_name', 'purchase_order_items.product_name as po_product_name', 'purchase_orders.po_number')
            ->leftJoin('products', 'supplier_payment_items.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'supplier_payment_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->leftJoin('purchase_order_items', 'supplier_payment_items.purchase_order_item_id', '=', 'purchase_order_items.id')
            ->leftJoin('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->where('supplier_payment_items.supplier_payment_id', $id)
            ->get();

        return response()->json([
            'status' => 'success',
            'payment' => $payment,
            'items' => $items,
        ]);
    }

    public function pay(Request $request)
    {
        if (!auth()->user()->hasPermission('access_finance_settlement') && !auth()->user()->hasPermission('access_finance')) {
            abort(403, 'Anda tidak memiliki akses ke Pelunasan Supplier.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'transaction_item_ids' => 'required|array|min:1',
            'transaction_item_ids.*' => 'required|integer|distinct|exists:transaction_items,id',
            'payment_date' => 'required|date',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string|max:1000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'payment_proof.max' => 'Ukuran file bukti pembayaran tidak boleh lebih dari 2 MB.',
            'payment_proof.uploaded' => 'File bukti pembayaran gagal diunggah, kemungkinan besar karena ukuran file terlalu besar melebihi batas sistem.',
            'payment_proof.mimes' => 'Format file bukti pembayaran harus berupa JPG, JPEG, PNG, atau PDF.',
        ]);

        DB::beginTransaction();
        try {
            $transactionItemIds = array_map('intval', $request->transaction_item_ids);
            $selectedProductIds = DB::table('transaction_items')
                ->whereIn('id', $transactionItemIds)
                ->pluck('product_id')
                ->unique()
                ->map(fn($id) => (int) $id)
                ->all();
            if (!$selectedProductIds) {
                throw new \InvalidArgumentException('Data transaksi yang dipilih tidak valid.');
            }

            $allocations = app(SupplierSettlementAllocationService::class)
                ->allocations(true, $selectedProductIds)
                ->filter(fn($allocation) => in_array((int) $allocation->transaction_item_id, $transactionItemIds, true)
                    && (int) $allocation->purchase_order_id === (int) $request->purchase_order_id
                    && (int) $allocation->supplier_id === (int) $request->supplier_id
                    && $allocation->purchase_order_item_id
                    && (!$request->filled('start_date')
                        || Carbon::parse($allocation->transaction_date)->toDateString() >= Carbon::parse($request->start_date)->toDateString())
                    && (!$request->filled('end_date')
                        || Carbon::parse($allocation->transaction_date)->toDateString() <= Carbon::parse($request->end_date)->toDateString()))
                ->values();

            if ($allocations->isEmpty()) {
                throw new \InvalidArgumentException('Tidak ada transaksi terpilih yang masih dapat dibayar pada PO ini. Muat ulang alokasi.');
            }
            $matchedTransactionItemIds = $allocations->pluck('transaction_item_id')->map(fn($id) => (int) $id)->unique()->all();
            if (array_diff($transactionItemIds, $matchedTransactionItemIds)) {
                throw new \InvalidArgumentException('Sebagian transaksi tidak lagi teralokasi ke PO ini. Muat ulang alokasi.');
            }

            $purchaseOrderItemIds = $allocations->pluck('purchase_order_item_id')->unique()->values();
            $purchaseOrderItems = DB::table('purchase_order_items')
                ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
                ->whereIn('purchase_order_items.id', $purchaseOrderItemIds)
                ->where('purchase_orders.supplier_id', $request->supplier_id)
                ->select('purchase_order_items.*', 'purchase_orders.po_number', 'purchase_orders.supplier_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($purchaseOrderItems->count() !== $purchaseOrderItemIds->count()) {
                throw new \InvalidArgumentException('Sebagian item PO tidak sesuai dengan supplier yang dipilih.');
            }
            if ($allocations->pluck('purchase_order_id')->unique()->count() !== 1
                || (int) $allocations->first()->purchase_order_id !== (int) $request->purchase_order_id) {
                throw new \InvalidArgumentException('Pembayaran hanya dapat memproses satu PO dalam satu waktu.');
            }

            $groupedItems = $allocations->groupBy(function ($allocation) {
                return implode('|', [
                    $allocation->purchase_order_item_id,
                    $allocation->product_id,
                    $allocation->product_variant_id ?? 'null',
                    number_format((float) $allocation->buy_price, 4, '.', ''),
                ]);
            });
            $totalAmount = round((float) $allocations->sum('total_hpp'), 2);
            if ($totalAmount <= 0) {
                throw new \InvalidArgumentException('Total pembayaran harus lebih besar dari nol.');
            }

            $receivedQuantities = DB::table('goods_receipt_items')
                ->select('purchase_order_item_id')
                ->selectRaw('SUM(quantity_received) as received_qty')
                ->whereIn('purchase_order_item_id', $purchaseOrderItemIds)
                ->groupBy('purchase_order_item_id')
                ->pluck('received_qty', 'purchase_order_item_id');
            foreach ($allocations->groupBy('purchase_order_item_id') as $purchaseOrderItemId => $items) {
                $purchaseOrderItem = $purchaseOrderItems->get($purchaseOrderItemId);
                $receivedQty = (float) ($receivedQuantities[$purchaseOrderItemId] ?? 0);
                if (!$purchaseOrderItem
                    || (float) $purchaseOrderItem->paid_qty + (float) $items->sum('allocated_qty') > $receivedQty + 0.0001) {
                    throw new \InvalidArgumentException('Jumlah pembayaran akan melebihi kuantitas yang sudah diterima pada PO.');
                }
            }

            $paymentProofPath = null;
            if ($request->hasFile('payment_proof')) {
                $paymentProofPath = $request->file('payment_proof')->store('supplier_payments', 'public');
            }

            $payDate = $request->payment_date;
            $month = date('m', strtotime($payDate));
            $year = date('y', strtotime($payDate));
            $prefix = "PAY{$month}{$year}-";

            $lastPayment = DB::table('supplier_payments')
                ->where('payment_number', 'like', $prefix . '%')
                ->orderBy('payment_number', 'desc')
                ->first();

            if ($lastPayment) {
                $lastNum = (int) substr($lastPayment->payment_number, -3);
                $newNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNum = '001';
            }

            $paymentNumber = $prefix . $newNum;
            $purchaseOrderIds = $allocations->pluck('purchase_order_id')->unique()->values();
            if (!$purchaseOrderIds->contains((int) $request->purchase_order_id)) {
                throw new \InvalidArgumentException('PO pembayaran tidak cocok dengan item yang dipilih.');
            }

            $paymentId = DB::table('supplier_payments')->insertGetId([
                'payment_number' => $paymentNumber,
                'supplier_id' => $request->supplier_id,
                'purchase_order_id' => $purchaseOrderIds->count() === 1 ? $purchaseOrderIds->first() : null,
                'payment_date' => $request->payment_date,
                'total_amount' => $totalAmount,
                'payment_proof' => $paymentProofPath,
                'notes' => $request->notes ?? null,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $paidByPurchaseOrder = [];
            $newPaidByTransactionItem = [];
            foreach ($groupedItems as $items) {
                $firstItem = $items->first();
                $qty = (float) $items->sum('allocated_qty');
                $buyPrice = (float) $firstItem->buy_price;
                $subtotal = round((float) $items->sum('total_hpp'), 2);
                $paymentItemId = DB::table('supplier_payment_items')->insertGetId([
                    'supplier_payment_id' => $paymentId,
                    'purchase_order_item_id' => $firstItem->purchase_order_item_id,
                    'product_id' => $firstItem->product_id,
                    'product_variant_id' => $firstItem->product_variant_id,
                    'qty' => $qty,
                    'buy_price' => $buyPrice,
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($items->groupBy('transaction_item_id') as $transactionItemId => $transactionAllocations) {
                    $allocatedQty = (float) $transactionAllocations->sum('allocated_qty');
                    DB::table('supplier_payment_item_allocations')->insert([
                        'supplier_payment_item_id' => $paymentItemId,
                        'transaction_item_id' => $transactionItemId,
                        'quantity' => $allocatedQty,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $newPaidByTransactionItem[$transactionItemId] = ($newPaidByTransactionItem[$transactionItemId] ?? 0) + $allocatedQty;
                }

                $purchaseOrderItemId = (int) $firstItem->purchase_order_item_id;
                $paidByPurchaseOrder[$firstItem->purchase_order_id] = ($paidByPurchaseOrder[$firstItem->purchase_order_id] ?? 0) + $subtotal;

                DB::table('purchase_order_items')
                    ->where('id', $purchaseOrderItemId)
                    ->increment('paid_qty', $qty);
            }

            $alreadyPaidByTransactionItem = DB::table('supplier_payment_item_allocations')
                ->whereIn('transaction_item_id', array_keys($newPaidByTransactionItem))
                ->select('transaction_item_id')
                ->selectRaw('SUM(quantity) as paid_quantity')
                ->groupBy('transaction_item_id')
                ->pluck('paid_quantity', 'transaction_item_id');
            $transactionQuantities = DB::table('transaction_items')
                ->whereIn('id', array_keys($newPaidByTransactionItem))
                ->pluck('qty', 'id');
            foreach ($newPaidByTransactionItem as $transactionItemId => $newQty) {
                if ((float) ($alreadyPaidByTransactionItem[$transactionItemId] ?? 0) >= (float) ($transactionQuantities[$transactionItemId] ?? 0)) {
                    DB::table('transaction_items')
                        ->where('id', $transactionItemId)
                        ->whereNull('supplier_payment_id')
                        ->update(['supplier_payment_id' => $paymentId]);
                }
            }

            foreach ($paidByPurchaseOrder as $purchaseOrderId => $amount) {
                DB::table('purchase_orders')->where('id', $purchaseOrderId)->increment('paid_amount', $amount);
                $this->updatePOStatus($purchaseOrderId);
            }

            DB::commit();
            $poNumbers = $purchaseOrderItems
                ->whereIn('id', $purchaseOrderItemIds)
                ->pluck('po_number')
                ->unique()
                ->implode(', ');
            session()->flash('success', 'Pembayaran ' . $paymentNumber . ' berhasil disimpan untuk PO ' . $poNumbers . '.');
            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil disimpan untuk PO ' . $poNumbers,
                'payment_id' => $paymentId,
                'payment_number' => $paymentNumber,
                'redirect' => route('admin.finance.settlement.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $status = $e instanceof \InvalidArgumentException ? 422 : 500;
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $status);
        }
    }

    private function updatePOStatus($poId)
    {
        $items = DB::table('purchase_order_items')->where('purchase_order_id', $poId)->get();
        $totalQty = 0;
        $totalPaid = 0;
        foreach ($items as $item) {
            $totalQty += $item->quantity;
            $totalPaid += $item->paid_qty;
        }

        $status = 'unpaid';
        if ($totalPaid >= $totalQty && $totalQty > 0) {
            $status = 'paid';
        } elseif ($totalPaid > 0) {
            $status = 'partial';
        }

        DB::table('purchase_orders')
            ->where('id', $poId)
            ->update([
                'payment_status' => $status,
            ]);
    }
}