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
                    <div class="dropdown d-inline dropleft">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true" data-toggle="dropdown">
                            Action
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="' . route('admin.purchasing.purchase_orders.show', $po->id) . '" class="dropdown-item">Detail</a></li>
                            <li><a href="' . route('admin.purchasing.purchase_orders.print', $po->id) . '" target="_blank" class="dropdown-item">Print PO</a></li>
                            <li><a href="#" data-id="' . $po->id . '" class="dropdown-item btn-delete text-danger">Hapus</a></li>
                        </ul>
                    </div>';
        })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $po_number = PurchaseOrder::generatePONumber();
        $suppliers = Supplier::where('status', 'active')->get();
        return view('admin.purchasing.purchase_orders.create', compact('po_number', 'suppliers'))->with('sb', 'PurchaseOrder');
    }

    public function getProducts(Request $request)
    {
        $search = $request->search;
        $products = Product::where('name', 'like', "%$search%")
            ->orWhereHas('variants', function ($q) use ($search) {
            $q->where('sku_code', 'like', "%$search%");
        })
            ->with(['variants' => function ($q) use ($search) {
        // Optional: filter variants if needed
        }])
            ->limit(20)
            ->get();

        $results = [];
        foreach ($products as $product) {
            if ($product->variants->count() > 0) {
                foreach ($product->variants as $variant) {
                    $name = $product->name;
                    if ($variant->variant_name && $variant->variant_name !== 'Default') {
                        $name .= ' (' . $variant->variant_name . ')';
                    }
                    $results[] = [
                        'id' => $name, // Use name as ID for easier handling with tags:true
                        'text' => $name,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'variant_name' => $variant->variant_name,
                        'price' => $variant->price_real,
                    ];
                }
            }
            else {
                $results[] = [
                    'id' => $product->name,
                    'text' => $product->name,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'variant_name' => '',
                    'price' => $product->price_real ?? 0,
                ];
            }
        }

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
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

        }
        catch (\Exception $e) {
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
