<?php

namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return view('admin.purchasing.purchase_orders.index')->with('sb', 'PurchaseOrder');
    }

    public function getall(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator']);

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('po_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('po_date', '<=', $request->end_date);
        }

        $pos = $query->orderBy('po_date', 'desc');

        return DataTables::of($pos)
            ->addIndexColumn()
            ->addColumn('supplier_name', function ($po) {
            return $po->supplier ? $po->supplier->name : '-';
        })
            ->addColumn('created_name', function ($po) {
            return $po->creator ? $po->creator->name : '-';
        })
            ->editColumn('po_date', function ($po) {
            return $po->po_date->format('d/m/Y');
        })
            ->addColumn('action', function ($po) {
                return '
                <div class="dropdown d-inline">
                    <button class="btn btn-primary dropdown-toggle btn-sm" type="button" data-toggle="dropdown">
                        Aksi
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item has-icon" href="' . route('admin.purchasing.purchase_orders.show', $po->id) . '">
                            <i class="fas fa-eye text-info"></i> Detail
                        </a>
                        <a class="dropdown-item has-icon" href="' . route('admin.purchasing.purchase_orders.edit', $po->id) . '">
                            <i class="fas fa-edit text-primary"></i> Edit
                        </a>
                        <a class="dropdown-item has-icon" href="' . route('admin.purchasing.purchase_orders.print', $po->id) . '" target="_blank">
                            <i class="fas fa-print text-success"></i> Print PO
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item has-icon btn-delete text-danger" href="#" data-id="' . $po->id . '">
                            <i class="fas fa-trash"></i> Hapus
                        </a>
                    </div>
                </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $po_number = PurchaseOrder::generatePONumber();
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();
        return view('admin.purchasing.purchase_orders.create', compact('po_number', 'suppliers', 'warehouses'))->with('sb', 'PurchaseOrder');
    }

    public function getProducts(Request $request)
    {
        $search = $request->search;
        $products = Product::with(['merek', 'variants.netto'])
            ->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhereHas('merek', function($mq) use ($search) {
                      $mq->where('name', 'like', "%$search%");
                  })
                  ->orWhereHas('variants', function ($vq) use ($search) {
                      $vq->where('sku_code', 'like', "%$search%");
                  });
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

                    // Combined text so far
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
                'po_number' => PurchaseOrder::generatePONumber(),
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'po_date' => $request->po_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'subtotal' => $request->subtotal,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'discount_amount' => $request->discount_amount,
                'tax_percentage' => $request->tax_percentage,
                'tax_amount' => $request->tax_amount,
                'total' => $request->total,
                'notes' => $request->notes,
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

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Purchase Order berhasil dibuat', 'redirect' => route('admin.purchasing.purchase_orders.index')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $po = PurchaseOrder::with('items.product')->findOrFail($id);
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();
        return view('admin.purchasing.purchase_orders.edit', compact('po', 'suppliers', 'warehouses'))->with('sb', 'PurchaseOrder');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
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
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'po_date' => $request->po_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'subtotal' => $request->subtotal,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'discount_amount' => $request->discount_amount,
                'tax_percentage' => $request->tax_percentage,
                'tax_amount' => $request->tax_amount,
                'total' => $request->total,
                'notes' => $request->notes,
            ]);

            // Simple update: Delete old items and re-create them
            PurchaseOrderItem::where('purchase_order_id', $po->id)->delete();

            foreach ($request->items as $item) {
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

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Purchase Order berhasil diperbarui', 'redirect' => route('admin.purchasing.purchase_orders.index')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $po = PurchaseOrder::with(['supplier', 'creator', 'items.product.merek'])->findOrFail($id);
        return view('admin.purchasing.purchase_orders.show', compact('po'))->with('sb', 'PurchaseOrder');
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
