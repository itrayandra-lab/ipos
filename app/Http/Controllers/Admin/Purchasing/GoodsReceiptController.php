<?php

namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class GoodsReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $restrictedMethods = ['create', 'store', 'edit', 'update', 'destroy'];

            if ($user && in_array($request->route()->getActionMethod(), $restrictedMethods)) {
                if (!$user->canEdit('access_goods_receipts')) {
                    if ($request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses untuk tindakan ini.'], 403);
                    }
                    return redirect()->route('admin.purchasing.goods_receipts.index')->with('error', 'Anda tidak memiliki akses untuk tindakan ini.');
                }
            }

            return $next($request);
        });
    }

    public function index()
    {
        return view('admin.purchasing.goods_receipts.index')->with('sb', 'GoodsReceipt');
    }

    public function getall()
    {
        $grs = GoodsReceipt::with([
            'supplier',
            'purchaseOrder',
            'receiver',
            'items.product.merek',
            'items.productVariant.netto',
            'items.productBatches.product.merek',
            'items.productBatches.variant.netto',
            'items.purchaseOrderItem.product.merek',
            'items.purchaseOrderItem.product.variants.netto'
        ])->orderBy('received_date', 'desc');

        return DataTables::of($grs)
            ->addIndexColumn()
            ->addColumn('dokumen', function ($gr) {
                $grNumber = '<span class="po-number">' . e($gr->sj_number) . '</span>';
                $poHtml = $gr->purchaseOrder
                    ? '<small class="text-muted d-block">' . e($gr->purchaseOrder->po_number) . '</small>'
                    : '<small class="text-muted d-block">-</small>';
                return $grNumber . $poHtml;
            })
            ->addColumn('surat_jalan', function ($gr) {
                $sj = '<span>' . e($gr->delivery_note_number ?? '-') . '</span>';
                $supplier = '<small class="text-muted d-block">' . e($gr->supplier ? $gr->supplier->name : '-') . '</small>';
                return $sj . $supplier;
            })
            ->addColumn('penerimaan', function ($gr) {
                $date = '<span>' . $gr->received_date->format('d/m/Y') . '</span>';
                $receiver = '<small class="text-muted d-block">' . e($gr->receiver ? $gr->receiver->name : '-') . '</small>';
                $badge = $gr->status === 'confirmed' ? 'success' : 'secondary';
                $status = '<br><span class="badge badge-status badge-' . $badge . '">' . strtoupper($gr->status) . '</span>';
                return $date . $receiver . $status;
            })
            ->addColumn('produk', function ($gr) {
                if ($gr->items->isEmpty()) {
                    return '-';
                }
                return $gr->items->map(function ($item) {
                    $parts = [];

                    // Try all sources to find best brand + name + netto
                    $brand = null;
                    $name = null;
                    $nettoText = null;

                    // Source 1: direct product relationship
                    if ($item->product) {
                        if ($item->product->merek) $brand = $item->product->merek->name;
                        $name = $item->product->name;
                        if ($item->productVariant && $item->productVariant->netto) {
                            $n = $item->productVariant->netto;
                            $nettoText = trim($n->netto_value . ' ' . $n->satuan);
                        }
                    }

                    // Source 2: via ProductBatch
                    if (!$brand || !$nettoText) {
                        $batch = $item->productBatches->first();
                        if ($batch && $batch->product) {
                            if (!$brand && $batch->product->merek) $brand = $batch->product->merek->name;
                            if (!$name) $name = $batch->product->name;
                            if (!$nettoText && $batch->variant && $batch->variant->netto) {
                                $n = $batch->variant->netto;
                                $nettoText = trim($n->netto_value . ' ' . $n->satuan);
                            }
                        }
                    }

                    // Source 3: via PurchaseOrderItem
                    if (!$brand || !$nettoText) {
                        if ($item->purchaseOrderItem && $item->purchaseOrderItem->product) {
                            $product = $item->purchaseOrderItem->product;
                            if (!$brand && $product->merek) $brand = $product->merek->name;
                            if (!$name) $name = $product->name;
                            if (!$nettoText) {
                                $variant = $product->variants->first();
                                if ($variant && $variant->netto) {
                                    $n = $variant->netto;
                                    $nettoText = trim($n->netto_value . ' ' . $n->satuan);
                                }
                            }
                        }
                    }

                    if ($brand) $parts[] = e($brand);
                    if ($name) $parts[] = e($name);
                    if ($nettoText) $parts[] = e($nettoText);

                    if ($parts) {
                        return '<div>' . implode(' ', $parts) . '</div>';
                    }
                    return '<div>' . e($item->product_name) . '</div>';
                })->implode('');
            })
            ->addColumn('action', function ($gr) {
                $isFinance = auth()->user()->isFinance();
                $editBtn = !$isFinance
                    ? '<a class="dropdown-item has-icon" href="' . route('admin.purchasing.goods_receipts.edit', $gr->id) . '"><i class="fas fa-edit text-warning"></i> Edit</a>'
                    : '';
                $deleteBtn = !$isFinance
                    ? '<div class="dropdown-divider"></div><a class="dropdown-item has-icon text-danger btn-delete" href="javascript:void(0)" data-id="' . $gr->id . '"><i class="fas fa-trash"></i> Hapus</a>'
                    : '';
                return '
                    <div class="dropdown d-inline dropleft">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">Aksi</button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item has-icon" href="' . route('admin.purchasing.goods_receipts.show', $gr->id) . '"><i class="fas fa-eye text-info"></i> Detail</a>
                            ' . $editBtn . '
                            ' . $deleteBtn . '
                        </div>
                    </div>';
            })
            ->rawColumns(['dokumen', 'surat_jalan', 'penerimaan', 'produk', 'action'])
            ->filterColumn('produk', function ($query, $keyword) {
                $query->whereHas('items', function ($q) use ($keyword) {
                    $q->where('product_name', 'like', "%{$keyword}%");
                });
            })
            ->make(true);
    }

    public function create()
    {
        $gr_number = GoodsReceipt::generateGRNumber();
        $pos = PurchaseOrder::whereIn('status', ['submitted', 'approved', 'partial'])->get();
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();
        $returns = $this->returnsWithRemaining();
        return view('admin.purchasing.goods_receipts.create', compact('gr_number', 'pos', 'suppliers', 'warehouses', 'returns'))->with('sb', 'GoodsReceipt');
    }

    /** Qty item return yang sudah diganti supplier (penerimaan pengganti), per item return. */
    private function replacedByReturnItem(?int $excludeGrId = null)
    {
        return DB::table('goods_receipt_items')
            ->whereNotNull('supplier_return_item_id')
            ->when($excludeGrId, fn($q) => $q->where('goods_receipt_id', '!=', $excludeGrId))
            ->selectRaw('supplier_return_item_id, SUM(quantity_received) as qty')
            ->groupBy('supplier_return_item_id')
            ->pluck('qty', 'supplier_return_item_id');
    }

    /** Return supplier yang masih punya sisa qty belum diganti. */
    private function returnsWithRemaining()
    {
        $replaced = $this->replacedByReturnItem();

        return \App\Models\SupplierReturn::with(['supplier', 'items'])
            ->orderByDesc('id')->limit(300)->get()
            ->map(function ($r) use ($replaced) {
                $r->remaining_total = $r->items->sum(fn($i) => max(0, $i->qty - (float) ($replaced[$i->id] ?? 0)));
                return $r;
            })
            ->filter(fn($r) => $r->remaining_total > 0)
            ->values();
    }

    /** Item return untuk penerimaan pengganti: qty return, sudah diganti, sisa, dan item PO terkait. */
    public function getReturnItems(Request $request)
    {
        $return = \App\Models\SupplierReturn::with(['items.product.merek', 'items.variant.netto', 'items.batch'])->findOrFail($request->return_id);
        $replaced = $this->replacedByReturnItem($request->exclude_gr ? (int) $request->exclude_gr : null);

        $items = $return->items->map(function ($i) use ($replaced) {
            $b = $i->batch;
            $poItemId = $b ? ($b->purchase_order_item_id ?: DB::table('goods_receipt_items')->where('id', $b->goods_receipt_item_id)->value('purchase_order_item_id')) : null;
            $done = (float) ($replaced[$i->id] ?? 0);
            $netto = $i->variant && $i->variant->netto ? trim($i->variant->netto->netto_value . ' ' . $i->variant->netto->satuan) : '';

            return [
                'supplier_return_item_id' => $i->id,
                'purchase_order_item_id' => $poItemId,
                'product_id' => $i->product_id,
                'variant_id' => $i->product_variant_id,
                'product_name' => trim(($i->product->merek->name ?? '') . ' ' . ($i->product->name ?? '')),
                'description' => $netto,
                'satuan' => $i->variant->netto->satuan ?? '',
                'batch_no' => $b->batch_no ?? '',
                'expiry_date' => $b && $b->expiry_date ? $b->expiry_date->format('Y-m') : '',
                'buy_price' => (float) $i->buy_price,
                'qty_returned' => (float) $i->qty,
                'qty_replaced' => $done,
                'qty_remaining' => max(0, (float) $i->qty - $done),
            ];
        })->filter(fn($x) => $x['qty_remaining'] > 0)->values();

        return response()->json([
            'return_number' => $return->return_number,
            'supplier_id' => $return->supplier_id,
            'warehouse_id' => $return->warehouse_id,
            'items' => $items,
        ]);
    }

    /** Pastikan qty pengganti tidak melebihi sisa qty item return. Return pesan error atau null. */
    private function replacementError(array $items, ?int $excludeGrId = null): ?string
    {
        $replaced = $this->replacedByReturnItem($excludeGrId);
        $incoming = [];
        foreach ($items as $it) {
            if (!empty($it['supplier_return_item_id'])) {
                $incoming[$it['supplier_return_item_id']] = ($incoming[$it['supplier_return_item_id']] ?? 0) + (float) $it['qty_received'];
            }
        }
        foreach ($incoming as $riId => $qty) {
            $ri = \App\Models\SupplierReturnItem::find($riId);
            if (!$ri) {
                return 'Item return tidak ditemukan.';
            }
            $remaining = (float) $ri->qty - (float) ($replaced[$riId] ?? 0);
            if ($qty > $remaining + 0.0001) {
                return "Qty pengganti melebihi sisa return (sisa {$remaining}).";
            }
        }

        return null;
    }

    public function getPoItems(Request $request)
    {
        $po = PurchaseOrder::with('items')->findOrFail($request->po_id);
        $items = $po->items->map(function ($item) {
            $data = $item->toArray();
            $data['display_name'] = $item->product_name;

            if ($item->product) {
                $parts = [];
                if ($item->product->merek) {
                    $parts[] = $item->product->merek->name;
                }
                $parts[] = $item->product->name;
                if ($item->description) {
                    $parts[] = $item->description;
                }
                $data['display_name'] = implode(' ', $parts);
            }

            return $data;
        });

        return response()->json([
            'supplier_id' => $po->supplier_id,
            'warehouse_id' => $po->warehouse_id,
            'items' => $items
        ]);
    }

    public function getProducts(Request $request)
    {
        $search = $request->search;
        $words = array_filter(explode(' ', $search));

        $products = Product::with(['merek', 'variants.netto', 'nettos'])
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

                    $pName = $product->name;
                    $vName = ($variant->variant_name && $variant->variant_name !== 'Default') ? $variant->variant_name : '';

                    $parts = [];
                    if ($brand) $parts[] = $brand;

                    if ($vName) {
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

                    if ($nettoText) {
                        $cleanCurrent = strtolower(str_replace(' ', '', $currentText));
                        $cleanNetto = strtolower(str_replace(' ', '', $nettoText));
                        if (strpos($cleanCurrent, $cleanNetto) === false) {
                            $parts[] = $nettoText;
                        }
                    }

                    $name = implode(' ', array_filter($parts));
                    $name = preg_replace('/\s+/', ' ', $name);

                    $results[] = [
                        'id' => $variant->id,
                        'text' => $name,
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'product_name' => trim($brand . ' ' . $product->name),
                        'variant_name' => $variant->variant_name ?? '',
                        'description' => $nettoText,
                        'satuan' => $variant->netto->satuan ?? '',
                    ];
                }
            } else {
                $nettoText = '';
                $firstNetto = $product->nettos->first();
                if ($firstNetto) {
                    $nettoText = trim($firstNetto->netto_value . ' ' . $firstNetto->satuan);
                }
                $parts = [];
                if ($brand) $parts[] = $brand;
                $parts[] = $product->name;
                if ($nettoText) {
                    $cleanName = strtolower(str_replace(' ', '', $product->name));
                    $cleanNetto = strtolower(str_replace(' ', '', $nettoText));
                    if (strpos($cleanName, $cleanNetto) === false) {
                        $parts[] = $nettoText;
                    }
                }
                $name = implode(' ', array_filter($parts));
                $name = preg_replace('/\s+/', ' ', $name);
                $results[] = [
                    'id' => 'p_' . $product->id,
                    'text' => $name,
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'product_name' => trim($brand . ' ' . $product->name),
                    'variant_name' => '',
                    'description' => $nettoText,
                    'satuan' => $firstNetto->satuan ?? '',
                ];
            }
        }

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $items = collect($request->items ?? [])->map(function ($item) {
            $item['expiry_date'] = normalize_expiry_date($item['expiry_date'] ?? null);
            return $item;
        })->all();
        $request->merge(['items' => $items]);

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'delivery_note_number' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'received_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.qty_received' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }
        if ($err = $this->replacementError($request->items)) {
            return response()->json(['status' => 'error', 'message' => $err], 422);
        }

        try {
            DB::beginTransaction();

            $gr = GoodsReceipt::create([
                'sj_number' => GoodsReceipt::generateGRNumber(),
                'purchase_order_id' => $request->purchase_order_id ?: null,
                'supplier_return_id' => $request->supplier_return_id ?: null,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'delivery_note_number' => $request->delivery_note_number,
                'delivery_date' => $request->delivery_date,
                'received_date' => $request->received_date,
                'received_by' => Auth::id(),
                'notes' => $request->notes,
                'status' => 'confirmed',
            ]);

            $poId = null;

            foreach ($request->items as $item) {
                $batchNo = $item['batch_no'] ?? null;

                $productId = null;
                $variantId = null;

                if (!empty($item['product_id'])) {
                    $productId = $item['product_id'];
                    $variantId = $item['variant_id'] ?? null;
                } elseif (!empty($item['purchase_order_item_id'])) {
                    $poItem = PurchaseOrderItem::with('product')->find($item['purchase_order_item_id']);
                    if ($poItem) {
                        $productId = $poItem->product_id;
                    }
                }

                if (!empty($item['purchase_order_item_id'])) {
                    $poItem = PurchaseOrderItem::find($item['purchase_order_item_id']);
                    if ($poItem) $poId = $poItem->purchase_order_id;
                }

                $grItem = GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'supplier_return_item_id' => $item['supplier_return_item_id'] ?? null,
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'product_name' => $item['product_name'],
                    'description' => $item['description'],
                    'satuan' => $item['satuan'] ?? null,
                    'batch_no' => $batchNo,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'quantity_ordered' => $item['qty_ordered'] ?? 0,
                    'quantity_received' => $item['qty_received'],
                    'quantity_difference' => $item['qty_received'] - ($item['qty_ordered'] ?? 0),
                    'notes' => $item['item_notes'] ?? null,
                ]);

                if ($productId) {
                    $buyPrice = !empty($item['buy_price']) ? $item['buy_price'] : 0;

                    if ($buyPrice <= 0) {
                        if (!empty($item['purchase_order_item_id'])) {
                            $poItem = \App\Models\PurchaseOrderItem::find($item['purchase_order_item_id']);
                            if ($poItem && $poItem->unit_price > 0) {
                                $buyPrice = $poItem->unit_price;
                            }
                        }
                    }

                    if ($buyPrice <= 0 && $variantId) {
                        $variant = ProductVariant::find($variantId);
                        if ($variant && $variant->product_hpp > 0) {
                            $buyPrice = $variant->product_hpp;
                        }
                    }

                    $finalBatchNo = $batchNo ?: 'GR-' . $grItem->id;
                    ProductBatch::create([
                        'goods_receipt_item_id' => $grItem->id,
                        'purchase_order_item_id' => $grItem->purchase_order_item_id,
                        'supplier_id' => $gr->supplier_id,
                        'product_id' => $productId,
                        'product_variant_id' => $variantId,
                        'warehouse_id' => $gr->warehouse_id,
                        'batch_no' => $finalBatchNo,
                        'expiry_date' => $item['expiry_date'] ?? null,
                        'qty' => (int)$item['qty_received'],
                        'buy_price' => $buyPrice,
                    ]);
                }
            }

            // Update PO status
            if ($poId) {
                $po = PurchaseOrder::with('items')->find($poId);
                if ($po) {
                    $allFullyReceived = true;
                    $anyReceived = false;

                    foreach ($po->items as $poItem) {
                        $totalReceived = GoodsReceiptItem::where('purchase_order_item_id', $poItem->id)->whereNull('supplier_return_item_id')->sum('quantity_received');
                        if ($totalReceived > 0) {
                            $anyReceived = true;
                        }
                        if ($totalReceived < $poItem->quantity) {
                            $allFullyReceived = false;
                        }
                    }

                    if ($allFullyReceived && $anyReceived) {
                        $po->update(['status' => 'received']);
                    } elseif ($anyReceived) {
                        $po->update(['status' => 'partial']);
                    } else {
                        $po->update(['status' => 'approved']);
                    }
                }
            }

            PurchaseOrderItem::syncReturned(collect($request->items)->pluck('purchase_order_item_id')->filter()->all());

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Penerimaan Barang berhasil dikonfirmasi', 'redirect' => route('admin.purchasing.goods_receipts.index')]);

        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $gr = GoodsReceipt::with(['supplier', 'purchaseOrder', 'receiver', 'items.product.merek', 'items.productVariant.netto', 'items.productBatches', 'items.purchaseOrderItem.product.merek', 'items.purchaseOrderItem.product.variants.netto'])->findOrFail($id);
        $pos = PurchaseOrder::whereIn('status', ['submitted', 'approved', 'partial', 'received'])->get();
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();
        return view('admin.purchasing.goods_receipts.edit', compact('gr', 'pos', 'suppliers', 'warehouses'))->with('sb', 'GoodsReceipt');
    }

    public function update(Request $request, $id)
    {
        $items = collect($request->items ?? [])->map(function ($item) {
            $item['expiry_date'] = normalize_expiry_date($item['expiry_date'] ?? null);
            return $item;
        })->all();
        $request->merge(['items' => $items]);

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'delivery_note_number' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'received_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.qty_received' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }
        if ($err = $this->replacementError($request->items, (int) $id)) {
            return response()->json(['status' => 'error', 'message' => $err], 422);
        }

        try {
            DB::beginTransaction();

            $gr = GoodsReceipt::with('items')->findOrFail($id);
            $oldPoItemIds = $gr->items->pluck('purchase_order_item_id')->filter()->all();

            // Delete old ProductBatch entries linked to this GR
            $poId = null;
            foreach ($gr->items as $oldItem) {
                if ($oldItem->purchase_order_item_id) {
                    $poItem = PurchaseOrderItem::find($oldItem->purchase_order_item_id);
                    if ($poItem) $poId = $poItem->purchase_order_id;
                }
                // Remove old FK-linked batches (skip batches already used in stock history)
                ProductBatch::where('goods_receipt_item_id', $oldItem->id)
                    ->get()
                    ->each(function ($batch) use ($oldItem) {
                        if ($batch->hasStockHistory()) {
                            $batch->update([
                                'goods_receipt_item_id' => null,
                                'purchase_order_item_id' => $batch->purchase_order_item_id ?: $oldItem->purchase_order_item_id,
                            ]);
                        } else {
                            $batch->delete();
                        }
                    });
                // Remove old manual batches (no FK)
                $oldProductId = null;
                if ($oldItem->purchase_order_item_id) {
                    $pi = PurchaseOrderItem::find($oldItem->purchase_order_item_id);
                    if ($pi) $oldProductId = $pi->product_id;
                }
                if ($oldProductId && $oldItem->batch_no) {
                    ProductBatch::where('product_id', $oldProductId)
                        ->where('batch_no', $oldItem->batch_no)
                        ->where('warehouse_id', $gr->warehouse_id)
                        ->whereNull('goods_receipt_item_id')
                        ->get()
                        ->each(function ($batch) {
                            if (!$batch->hasStockHistory()) {
                                $batch->delete();
                            }
                        });
                }
            }

            $gr->items()->delete();

            $gr->update([
                'purchase_order_id' => $request->purchase_order_id ?: null,
                'supplier_return_id' => $request->supplier_return_id ?: $gr->supplier_return_id,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'delivery_note_number' => $request->delivery_note_number,
                'delivery_date' => $request->delivery_date,
                'received_date' => $request->received_date,
                'notes' => $request->notes,
            ]);

            $newPoId = null;

            foreach ($request->items as $item) {
                $batchNo = $item['batch_no'] ?? null;

                $productId = null;
                $variantId = null;

                if (!empty($item['product_id'])) {
                    $productId = $item['product_id'];
                    $variantId = $item['variant_id'] ?? null;
                } elseif (!empty($item['purchase_order_item_id'])) {
                    $poItem = PurchaseOrderItem::with('product')->find($item['purchase_order_item_id']);
                    if ($poItem) {
                        $productId = $poItem->product_id;
                    }
                }

                if (!empty($item['purchase_order_item_id'])) {
                    $poItem = PurchaseOrderItem::find($item['purchase_order_item_id']);
                    if ($poItem) $newPoId = $poItem->purchase_order_id;
                }

                $grItem = GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'supplier_return_item_id' => $item['supplier_return_item_id'] ?? null,
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'product_name' => $item['product_name'],
                    'description' => $item['description'],
                    'satuan' => $item['satuan'] ?? null,
                    'batch_no' => $batchNo,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'quantity_ordered' => $item['qty_ordered'] ?? 0,
                    'quantity_received' => $item['qty_received'],
                    'quantity_difference' => $item['qty_received'] - ($item['qty_ordered'] ?? 0),
                    'notes' => $item['item_notes'] ?? null,
                ]);

                if ($productId) {
                    $buyPrice = !empty($item['buy_price']) ? $item['buy_price'] : 0;

                    if ($buyPrice <= 0) {
                        if (!empty($item['purchase_order_item_id'])) {
                            $poItem = \App\Models\PurchaseOrderItem::find($item['purchase_order_item_id']);
                            if ($poItem && $poItem->unit_price > 0) {
                                $buyPrice = $poItem->unit_price;
                            }
                        }
                    }

                    if ($buyPrice <= 0 && $variantId) {
                        $variant = ProductVariant::find($variantId);
                        if ($variant && $variant->product_hpp > 0) {
                            $buyPrice = $variant->product_hpp;
                        }
                    }

                    $finalBatchNo = $batchNo ?: 'GR-' . $grItem->id;
                    $existingBatch = ProductBatch::where('product_id', $productId)
                        ->where('product_variant_id', $variantId)
                        ->where('warehouse_id', $gr->warehouse_id)
                        ->where('batch_no', $finalBatchNo)
                        ->where('purchase_order_item_id', $grItem->purchase_order_item_id)
                        ->first();

                    if ($existingBatch) {
                        $existingBatch->update([
                            'goods_receipt_item_id' => $grItem->id,
                            'supplier_id' => $gr->supplier_id,
                            'qty' => (int)$item['qty_received'],
                            'buy_price' => $buyPrice,
                            'expiry_date' => $item['expiry_date'] ?? $existingBatch->expiry_date,
                        ]);
                    } else {
                        ProductBatch::create([
                            'goods_receipt_item_id' => $grItem->id,
                            'purchase_order_item_id' => $grItem->purchase_order_item_id,
                            'supplier_id' => $gr->supplier_id,
                            'product_id' => $productId,
                            'product_variant_id' => $variantId,
                            'warehouse_id' => $gr->warehouse_id,
                            'batch_no' => $finalBatchNo,
                            'expiry_date' => $item['expiry_date'] ?? null,
                            'qty' => (int)$item['qty_received'],
                            'buy_price' => $buyPrice,
                        ]);
                    }
                }
            }

            // Update PO status
            $poId = $poId ?: $newPoId;
            if ($poId) {
                $po = PurchaseOrder::with('items')->find($poId);
                if ($po) {
                    $allFullyReceived = true;
                    $anyReceived = false;

                    foreach ($po->items as $poItem) {
                        $totalReceived = GoodsReceiptItem::where('purchase_order_item_id', $poItem->id)->whereNull('supplier_return_item_id')->sum('quantity_received');
                        if ($totalReceived > 0) {
                            $anyReceived = true;
                        }
                        if ($totalReceived < $poItem->quantity) {
                            $allFullyReceived = false;
                        }
                    }

                    if ($allFullyReceived && $anyReceived) {
                        $po->update(['status' => 'received']);
                    } elseif ($anyReceived) {
                        $po->update(['status' => 'partial']);
                    } else {
                        $po->update(['status' => 'approved']);
                    }
                }
            }

            PurchaseOrderItem::syncReturned(array_merge($oldPoItemIds, collect($request->items)->pluck('purchase_order_item_id')->filter()->all()));

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Penerimaan Barang berhasil diperbarui', 'redirect' => route('admin.purchasing.goods_receipts.index')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $gr = GoodsReceipt::with('items')->findOrFail($id);
            $affectedPoItemIds = $gr->items->pluck('purchase_order_item_id')->filter()->all();

            $poId = null;

            // Clean up ProductBatch entries before deleting items
            foreach ($gr->items as $item) {
                if ($item->purchase_order_item_id) {
                    $poItem = PurchaseOrderItem::find($item->purchase_order_item_id);
                    if ($poItem) $poId = $poItem->purchase_order_id;
                }

                ProductBatch::where('goods_receipt_item_id', $item->id)
                    ->get()
                    ->each(function ($batch) use ($item) {
                        if ($batch->hasStockHistory()) {
                            $batch->update([
                                'goods_receipt_item_id' => null,
                                'purchase_order_item_id' => $batch->purchase_order_item_id ?: $item->purchase_order_item_id,
                            ]);
                        } else {
                            $batch->delete();
                        }
                    });

                // Also clean up old manual batches where FK was null
                $productId = null;
                if ($item->purchase_order_item_id) {
                    $pi = PurchaseOrderItem::find($item->purchase_order_item_id);
                    if ($pi) $productId = $pi->product_id;
                }
                if ($productId && $item->batch_no) {
                    ProductBatch::where('product_id', $productId)
                        ->where('batch_no', $item->batch_no)
                        ->where('warehouse_id', $gr->warehouse_id)
                        ->whereNull('goods_receipt_item_id')
                        ->get()
                        ->each(function ($batch) {
                            if (!$batch->hasStockHistory()) {
                                $batch->delete();
                            }
                        });
                }
            }

            $gr->items()->delete();
            $gr->delete();

            // Update PO status
            if ($poId) {
                $po = PurchaseOrder::with('items')->find($poId);
                if ($po) {
                    $allFullyReceived = true;
                    $anyReceived = false;

                    foreach ($po->items as $poItem) {
                        $totalReceived = GoodsReceiptItem::where('purchase_order_item_id', $poItem->id)->whereNull('supplier_return_item_id')->sum('quantity_received');
                        if ($totalReceived > 0) {
                            $anyReceived = true;
                        }
                        if ($totalReceived < $poItem->quantity) {
                            $allFullyReceived = false;
                        }
                    }

                    if ($allFullyReceived && $anyReceived) {
                        $po->update(['status' => 'received']);
                    } elseif ($anyReceived) {
                        $po->update(['status' => 'partial']);
                    } else {
                        $po->update(['status' => 'approved']);
                    }
                }
            }

            PurchaseOrderItem::syncReturned($affectedPoItemIds);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Penerimaan Barang berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $gr = GoodsReceipt::with(['supplier', 'purchaseOrder', 'receiver', 'items.product.merek', 'items.productVariant.netto', 'items.productBatches.product.merek', 'items.productBatches.variant.netto', 'items.purchaseOrderItem.product.merek', 'items.purchaseOrderItem.product.variants.netto'])->findOrFail($id);
        return view('admin.purchasing.goods_receipts.show', compact('gr'))->with('sb', 'GoodsReceipt');
    }
}
