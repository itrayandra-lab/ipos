<?php

namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use App\Models\PurchaseOrderApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        $baseQuery = PurchaseOrder::query();

        if ($request->filled('supplier_id')) {
            $baseQuery->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $baseQuery->whereDate('po_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $baseQuery->whereDate('po_date', '<=', $request->end_date);
        }

        $totalPembelian = (float) (clone $baseQuery)->sum('total');

        $poIds = (clone $baseQuery)->pluck('id');

        $totalTerbayar = 0;
        if ($poIds->isNotEmpty()) {
            $productIds = PurchaseOrderItem::whereIn('purchase_order_id', $poIds)
                ->whereNotNull('product_id')
                ->pluck('product_id')
                ->unique()
                ->toArray();

            if (!empty($productIds)) {
                $totalTerbayar = (float) DB::table('supplier_payment_items')
                    ->whereIn('product_id', $productIds)
                    ->sum('subtotal');
            }
        }

        $sisa = max(0, $totalPembelian - $totalTerbayar);

        return view('admin.purchasing.purchase_orders.index', compact(
            'suppliers', 'totalPembelian', 'totalTerbayar', 'sisa'
        ))->with('sb', 'PurchaseOrder');
    }

    public function getall(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator', 'items', 'approvals.user']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('po_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('po_date', '<=', $request->end_date);
        }

        $pos = $query->orderBy('po_date', 'desc');

        return DataTables::of($pos)
            ->addIndexColumn()
            ->addColumn('informasi_po', function ($po) {
                $poNumber = '<span class="po-number">' . e($po->po_number) . '</span>';
                $supplier = '<small class="text-muted d-block">' . e($po->supplier ? $po->supplier->name : '-') . '</small>';
                return $poNumber . $supplier;
            })
            ->addColumn('produk', function ($po) {
                if ($po->items->isEmpty()) {
                    return '-';
                }
                return $po->items->map(function ($item) {
                    $name = e($item->product_name);
                    $qty = number_format($item->quantity, 0);
                    $satuan = e($item->satuan ?? 'pcs');
                    $price = 'Rp ' . number_format($item->unit_price, 0, ',', '.');
                    return '<div class="mb-1">' . $name . '</div>';
                })->implode('');
            })
            ->addColumn('detail_transaksi', function ($po) {
                $total = '<div class="font-weight-bold">Rp ' . number_format($po->total, 0, ',', '.') . '</div>';
                $classes = [
                    'draft' => 'bg-soft-draft',
                    'submitted' => 'bg-soft-submitted',
                    'approved' => 'bg-soft-approved',
                    'partial' => 'bg-soft-partial',
                    'received' => 'bg-soft-received',
                    'cancelled' => 'bg-soft-cancelled'
                ];
                $labels = [
                    'draft' => 'DRAFT',
                    'submitted' => 'DIKIRIM',
                    'approved' => 'DISETUJUI',
                    'partial' => 'SEBAGIAN',
                    'received' => 'DITERIMA',
                    'cancelled' => 'DIBATALKAN'
                ];
                $label = $labels[$po->status] ?? strtoupper($po->status);
                $class = $classes[$po->status] ?? 'bg-soft-draft';
                $status = '<span class="badge badge-status ' . $class . '">' . $label . '</span>';
                return $total . $status;
            })
            ->addColumn('informasi_admin', function ($po) {
                $date = '<span>' . $po->po_date->format('d/m/Y') . '</span>';
                $pic = '<small class="text-muted d-block">' . e($po->creator ? $po->creator->name : '-') . '</small>';

                $totalAp = $po->approvals->count();
                $approvedAp = $po->approvals->where('status', 'approved')->count();
                $rejectedAp = $po->approvals->where('status', 'rejected')->count();
                $pendingAp = $po->approvals->where('status', 'pending')->count();

                if ($totalAp === 0) {
                    $verifBadge = '<span class="badge badge-status bg-soft-gray mt-1">—</span>';
                } elseif ($rejectedAp > 0) {
                    $verifBadge = '<span class="badge badge-status bg-soft-danger mt-1"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>';
                } elseif ($approvedAp === $totalAp) {
                    $verifBadge = '<span class="badge badge-status bg-soft-success mt-1"><i class="fas fa-check-circle mr-1"></i> Terverifikasi</span>';
                } else {
                    $verifBadge = '<span class="badge badge-status bg-soft-warning mt-1"><i class="fas fa-clock mr-1"></i> Menunggu</span>';
                }

                return $date . $pic . $verifBadge;
            })
            ->addColumn('action', function ($po) {
                $isFinance = auth()->user()->isFinance();

                $totalAp = $po->approvals->count();
                $approvedAp = $po->approvals->where('status', 'approved')->count();
                $isVerified = $totalAp > 0 && $approvedAp === $totalAp;

                $editBtn = !$isFinance ? '
                        <a class="dropdown-item has-icon" href="' . route('admin.purchasing.purchase_orders.edit', $po->id) . '">
                            <i class="fas fa-edit text-primary"></i> Edit
                        </a>' : '';
                $deleteBtn = !$isFinance ? '
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item has-icon btn-delete text-danger" href="#" data-id="' . $po->id . '">
                            <i class="fas fa-trash"></i> Hapus
                        </a>' : '';

                $printBtn = $isVerified
                    ? '<a class="dropdown-item has-icon" href="' . route('admin.purchasing.purchase_orders.print', $po->id) . '" target="_blank">
                            <i class="fas fa-print text-success"></i> Print PO
                        </a>'
                    : '<a class="dropdown-item has-icon disabled-link" href="#" onclick="event.preventDefault();iziToast.warning({title:\'Tidak bisa\',message:\'PO harus terverifikasi semua persetujuan sebelum print\',position:\'topRight\'});">
                            <i class="fas fa-print text-muted"></i> Print PO
                        </a>';

                return '
                <div class="dropdown d-inline">
                    <button class="btn btn-primary dropdown-toggle btn-sm" type="button" data-toggle="dropdown">
                        Aksi
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item has-icon" href="' . route('admin.purchasing.purchase_orders.show', $po->id) . '">
                            <i class="fas fa-eye text-info"></i> Detail
                        </a>
                        ' . $editBtn . '
                        ' . $printBtn . '
                        ' . $deleteBtn . '
                    </div>
                </div>';
            })
            ->rawColumns(['informasi_po', 'produk', 'detail_transaksi', 'informasi_admin', 'action'])
            ->filterColumn('produk', function ($query, $keyword) {
                $query->whereHas('items', function ($q) use ($keyword) {
                    $q->where('product_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('detail_transaksi', function ($query, $keyword) {
                $query->where('total', 'like', "%{$keyword}%")
                      ->orWhere('status', 'like', "%{$keyword}%");
            })
            ->make(true);
    }

    public function create()
    {
        $po_number = PurchaseOrder::generatePONumber();
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();
        $users = User::whereIn('role', ['super_admin', 'store_manager', 'admin'])->orderBy('name')->get();
        return view('admin.purchasing.purchase_orders.create', compact('po_number', 'suppliers', 'warehouses', 'users'))->with('sb', 'PurchaseOrder');
    }

    public function getProducts(Request $request)
    {
        $search = $request->search;
        $words = array_filter(explode(' ', $search));

        $products = Product::with(['merek', 'variants.netto'])
            ->when($words, function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($subQ) use ($word) {
                        $subQ->where('name', 'like', "%{$word}%")
                            ->orWhereHas('merek', function ($mq) use ($word) {
                                $mq->where('name', 'like', "%{$word}%");
                            })
                            ->orWhereHas('variants', function ($vq) use ($word) {
                                $vq->where('sku_code', 'like', "%{$word}%");
                            });
                    });
                }
            })
            ->limit(20)
            ->get();

        $results = [];
        foreach ($products as $product) {
            $brand = ($product->merek && $product->merek->name) ? $product->merek->name : '';
            
            if ($product->variants->count() > 0) {
                foreach ($product->variants as $variant) {
                    $nettoText = '';
                    if ($variant->netto) {
                        $nettoText = trim($variant->netto->netto_value . ' ' . $variant->netto->satuan);
                    }

                    $parts = [];
                    if ($brand) $parts[] = $brand;

                    $pName = $product->name;
                    $vName = ($variant->variant_name && $variant->variant_name !== 'Default') ? $variant->variant_name : '';

                    $parts = [];
                    if ($brand) $parts[] = $brand;

                    // Logic to avoid redundancy between product and variant
                    if ($vName) {
                        // If variant name already contains product name, just use variant name
                        if (stripos($vName, $pName) !== false) {
                            $parts[] = $vName;
                        } else {
                            $parts[] = $pName;
                            $parts[] = $vName;
                        }
                    } else {
                        $parts[] = $pName;
                    }

                    $currentText = implode(' ', $parts);
                    
                    // Logic to avoid redundancy with netto
                    if ($nettoText) {
                        // Clean spaces to check for presence
                        $cleanCurrent = strtolower(str_replace(' ', '', $currentText));
                        $cleanNetto = strtolower(str_replace(' ', '', $nettoText));
                        
                        if (strpos($cleanCurrent, $cleanNetto) === false) {
                            $parts[] = $nettoText;
                        }
                    }

                    $name = implode(' ', array_filter($parts));
                    $name = preg_replace('/\s+/', ' ', $name);

                    $results[] = [
                        'id' => $name,
                        'text' => $name,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'variant_name' => $variant->variant_name,
                        'price' => $variant->price_real ?? 0,
                        'description' => $nettoText
                    ];
                }
            } else {
                $name = trim($brand . ' ' . $product->name);
                $results[] = [
                    'id' => $name,
                    'text' => $name,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'variant_name' => '',
                    'price' => $product->price_real ?? 0,
                    'description' => ''
                ];
            }
        }

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'po_number' => 'required|string|max:50',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'po_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $po = PurchaseOrder::create([
                'po_number' => $request->po_number,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'po_date' => $request->po_date,
                'expected_delivery_date' => $request->expected_delivery_date ?: null,
                'subtotal' => $request->subtotal,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'discount_amount' => $request->discount_amount,
                'tax_percentage' => $request->tax_percentage,
                'tax_amount' => $request->tax_amount,
                'total' => $request->total,
                'notes' => $request->notes ?: null,
                'status' => 'submitted',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                // If product_id is not provided or invalid, we still save the product_name
                $productId = null;
                if (!empty($item['product_id']) && is_numeric($item['product_id'])) {
                    $productId = $item['product_id'];
                }

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $productId,
                    'product_name' => $item['product_name'],
                    'description' => $item['description'],
                    'satuan' => $item['satuan'] ?? null,
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'total' => $item['qty'] * $item['price'],
                ]);
            }

            if ($request->filled('approvers') && is_array($request->approvers)) {
                foreach ($request->approvers as $userId) {
                    PurchaseOrderApproval::create([
                        'purchase_order_id' => $po->id,
                        'user_id' => $userId,
                        'token' => Str::random(64),
                        'status' => 'pending',
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Purchase Order berhasil dibuat', 'redirect' => route('admin.purchasing.purchase_orders.show', $po->id)]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $po = PurchaseOrder::with(['items.product', 'approvals'])->findOrFail($id);
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();
        $users = User::whereIn('role', ['super_admin', 'store_manager', 'admin'])->orderBy('name')->get();
        return view('admin.purchasing.purchase_orders.edit', compact('po', 'suppliers', 'warehouses', 'users'))->with('sb', 'PurchaseOrder');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'po_number' => 'required|string|max:50',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'po_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $po = PurchaseOrder::findOrFail($id);
            $po->update([
                'po_number' => $request->po_number,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'po_date' => $request->po_date,
                'expected_delivery_date' => $request->expected_delivery_date ?: null,
                'subtotal' => $request->subtotal,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'discount_amount' => $request->discount_amount,
                'tax_percentage' => $request->tax_percentage,
                'tax_amount' => $request->tax_amount,
                'total' => $request->total,
                'notes' => $request->notes ?: null,
            ]);

            // Update items in-place to preserve IDs (GR items reference purchase_order_item_id)
            $existingItems = $po->items()->orderBy('id')->get();
            $existingCount = $existingItems->count();

            // Re-index to 0-based (form HTML uses 1-based indexing from rowCount++)
            $items = array_values($request->items);

            foreach ($items as $i => $item) {
                $productId = null;
                if (!empty($item['product_id']) && is_numeric($item['product_id'])) {
                    $productId = $item['product_id'];
                }

                $data = [
                    'product_id' => $productId,
                    'product_name' => $item['product_name'],
                    'description' => $item['description'],
                    'satuan' => $item['satuan'] ?? null,
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'total' => $item['qty'] * $item['price'],
                ];

                if ($i < $existingCount) {
                    $existingItems[$i]->update($data);
                } else {
                    $po->items()->create(array_merge($data, ['purchase_order_id' => $po->id]));
                }
            }

            // Remove extra items if user deleted some rows (using 0-based index)
            if (count($items) < $existingCount) {
                for ($i = count($items); $i < $existingCount; $i++) {
                    $existingItems[$i]->delete();
                }
            }

            // Sync approvers — only reset if still pending
            if ($request->has('approvers') && is_array($request->approvers)) {
                $newUserIds = collect($request->approvers)->map(fn($v) => (int) $v);
                $po->load('approvals');

                // Remove approvers no longer selected (only if still pending)
                foreach ($po->approvals as $app) {
                    if ($app->status === 'pending' && !$newUserIds->contains($app->user_id)) {
                        $app->delete();
                    }
                }

                // Add new approvers
                $existingUserIds = $po->approvals->where('status', 'pending')->pluck('user_id');
                foreach ($newUserIds as $userId) {
                    if (!$existingUserIds->contains($userId)) {
                        PurchaseOrderApproval::create([
                            'purchase_order_id' => $po->id,
                            'user_id' => $userId,
                            'token' => Str::random(64),
                            'status' => 'pending',
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Purchase Order berhasil diperbarui', 'redirect' => route('admin.purchasing.purchase_orders.index')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $po = PurchaseOrder::with([
            'supplier',
            'creator',
            'items.product.merek',
            'items.goodsReceiptItems',
            'goodsReceipts.items',
            'goodsReceipts.receiver',
            'approvals.user'
        ])->findOrFail($id);

        $productIds = $po->items->pluck('product_id')->filter()->unique()->toArray();

        $payments = collect();
        $totalPaid = 0;
        if (!empty($productIds)) {
            $paymentRecords = DB::table('supplier_payment_items')
                ->join('supplier_payments', 'supplier_payment_items.supplier_payment_id', '=', 'supplier_payments.id')
                ->join('products', 'supplier_payment_items.product_id', '=', 'products.id')
                ->leftJoin('users', 'supplier_payments.created_by', '=', 'users.id')
                ->whereIn('supplier_payment_items.product_id', $productIds)
                ->select(
                    'supplier_payments.id as payment_id',
                    'supplier_payments.payment_number',
                    'supplier_payments.payment_date',
                    'supplier_payments.total_amount as payment_total',
                    'supplier_payments.payment_proof',
                    'supplier_payments.notes',
                    'supplier_payment_items.product_id',
                    'supplier_payment_items.qty',
                    'supplier_payment_items.buy_price',
                    'supplier_payment_items.subtotal',
                    'products.name as product_name',
                    'users.name as cashier_name'
                )
                ->orderBy('supplier_payments.payment_date')
                ->get();

            $payments = $paymentRecords;
            $totalPaid = $paymentRecords->sum('subtotal');
        }

        // Build merged timeline: goods receipts + payments
        $timeline = collect();
        foreach ($po->goodsReceipts as $gr) {
            $timeline->push([
                'type' => 'receipt',
                'date' => $gr->received_date ?? $gr->created_at,
                'label' => 'Penerimaan Barang',
                'reference' => $gr->sj_number,
                'details' => $gr->items->map(fn($i) => $i->product_name . ' (' . number_format($i->quantity_received, 0) . ' ' . ($i->satuan ?? 'pcs') . ')')->implode(', '),
                'actor' => $gr->receiver->name ?? '-',
            ]);
        }
        foreach ($payments->groupBy('payment_id') as $pid => $group) {
            $first = $group->first();
            $itemLines = $group->map(fn($i) => $i->product_name . ' (' . number_format($i->qty, 0) . ' pcs × Rp ' . number_format($i->buy_price, 0, ',', '.') . ')')->implode(', ');
            $timeline->push([
                'type' => 'payment',
                'date' => $first->payment_date,
                'label' => 'Pembayaran',
                'reference' => $first->payment_number ?? 'PAY-' . $pid,
                'details' => 'Rp ' . number_format($group->sum('subtotal'), 0, ',', '.') . ' — ' . $itemLines,
                'actor' => $first->cashier_name ?? '-',
            ]);
        }
        $timeline = $timeline->sortBy('date')->values();

        $totalPo = (float) $po->total;
        $outstanding = max(0, $totalPo - $totalPaid);
        $progressPct = $totalPo > 0 ? min(100, round(($totalPaid / $totalPo) * 100)) : 0;

        return view('admin.purchasing.purchase_orders.show', compact('po', 'payments', 'totalPaid', 'outstanding', 'progressPct', 'timeline'))->with('sb', 'PurchaseOrder');
    }

    public function print($id)
    {
        $po = PurchaseOrder::with(['supplier', 'items.product.merek'])->findOrFail($id);
        $storeSetting = \App\Models\StoreSetting::find(1);
        return view('admin.purchasing.purchase_orders.print', compact('po', 'storeSetting'));
    }



    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();
            $po = PurchaseOrder::findOrFail($request->id);

            // Optional: check status, maybe only draft/submitted can be deleted
            // if ($po->status === 'received') {
            //     return response()->json(['status' => 'error', 'message' => 'PO yang sudah diterima tidak dapat dihapus'], 422);
            // }

            // Delete items first
            PurchaseOrderItem::where('purchase_order_id', $po->id)->delete();

            // Delete PO
            $po->delete();

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Purchase Order berhasil dihapus']);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
