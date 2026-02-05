<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\Validator;

class BatchController extends Controller
{
    public function index($productId)
    {
        $product = Product::findOrFail($productId);
        $batches = $product->batches()->orderBy('expiry_date', 'asc')->get();
        return view('admin.manage_master.products.batches', compact('product', 'batches'))->with('sb', 'Product');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'batch_no' => 'required|string',
            'expiry_date' => 'required|date',
            'qty' => 'required|integer|min:1',
            'buy_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        ProductBatch::create($request->all());

        return redirect()->back()->with('message', 'Batch berhasil ditambahkan');
    }

    public function update(Request $request)
    {
        $batch = ProductBatch::findOrFail($request->id);
        
        $validator = Validator::make($request->all(), [
            'qty' => 'required|integer|min:0',
            'batch_no' => 'required|string',
            'expiry_date' => 'required|date',
            'buy_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $batch->update($request->only(['qty', 'batch_no', 'expiry_date', 'buy_price']));

        return redirect()->back()->with('message', 'Data batch berhasil diperbarui');
    }

    public function delete(Request $request)
    {
        $batch = ProductBatch::findOrFail($request->id);
        $batch->delete();

        return response()->json(['message' => 'Batch berhasil dihapus'], 200);
    }
}
