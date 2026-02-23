<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use App\Http\Controllers\Controller;
use App\Models\ProductBatch;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();
        return view('admin.manage_master.stock.index', compact('products'))->with('sb', 'Stock');
    }

    public function getall(Request $request)
    {
        $batches = ProductBatch::with(['product.merek', 'variant.netto'])
            ->select('product_batches.*');

        return DataTables::of($batches)
            ->addIndexColumn()
            ->addColumn('product_name', function ($batch) {
            if ($batch->product) {
                $merek = $batch->product->merek ? $batch->product->merek->name . ' ' : '';
                return $merek . $batch->product->name;
            }
            return '-';
        })
            ->addColumn('netto', function ($batch) {
            if ($batch->variant && $batch->variant->netto) {
                $netto = $batch->variant->netto->netto_value;
                $satuan = $batch->variant->netto->satuan ?? '';
                return $netto . $satuan;
            }
            return '-';
        })
            ->addColumn('current_stock', function ($batch) {
            $sold = $batch->transactionItems()->sum('qty');
            $current = $batch->qty - $sold;
            return $current;
        })
            ->addColumn('action', function ($batch) {
            return '
                <div class="dropdown d-inline dropleft">
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true" data-toggle="dropdown">
                        Action
                    </button>
                    <ul class="dropdown-menu">
                        <li><a data-id="' . $batch->id . '" class="dropdown-item btn-edit">Edit</a></li>
                        <li><a data-id="' . $batch->id . '" class="dropdown-item btn-delete" href="#">Hapus</a></li>
                    </ul>
                </div>
                ';
        })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'batch_no' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
            'qty' => 'required|integer|min:1',
            'buy_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        ProductBatch::create($request->all());

        return response()->json(['success' => true, 'message' => 'Batch stok berhasil ditambahkan']);
    }

    public function get(Request $request)
    {
        $batch = ProductBatch::with(['product', 'variant'])->findOrFail($request->id);
        return response()->json(['success' => true, 'data' => $batch]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:product_batches,id',
            'batch_no' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
            'qty' => 'required|integer|min:0',
            'buy_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $batch = ProductBatch::findOrFail($request->id);
        $batch->update($request->only(['batch_no', 'expiry_date', 'qty', 'buy_price']));

        return response()->json(['success' => true, 'message' => 'Data batch berhasil diperbarui']);
    }

    public function delete(Request $request)
    {
        $batch = ProductBatch::findOrFail($request->id);

        // Check if batch has been used in transactions
        if ($batch->transactionItems()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Batch tidak dapat dihapus karena sudah digunakan dalam transaksi'], 422);
        }

        $batch->delete();

        return response()->json(['success' => true, 'message' => 'Batch berhasil dihapus']);
    }

    public function getVariants(Request $request)
    {
        $variants = ProductVariant::whereHas('netto', function ($q) use ($request) {
            $q->where('product_id', $request->product_id);
        })
            ->with('netto')
            ->get()
            ->map(function ($variant) {
            return [
            'id' => $variant->id,
            'sku_code' => $variant->sku_code,
            'price' => $variant->price,
            'netto_value' => $variant->netto ? $variant->netto->netto_value : '-',
            'satuan' => $variant->netto ? $variant->netto->satuan : '',
            ];
        });

        return response()->json(['success' => true, 'data' => $variants]);
    }
}
