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
        $request->merge(['expiry_date' => normalize_expiry_date($request->expiry_date)]);
        $request->merge(['buy_price' => self::parsePrice($request->input('buy_price'))]);

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

        // Anti input ganda: kombinasi produk+varian+gudang+no batch yang sama = baris yang sama
        $dup = ProductBatch::findDuplicate(
            $request->product_id,
            $request->product_variant_id,
            $request->warehouse_id,
            $request->batch_no
        );
        if ($dup) {
            return redirect()->back()->withErrors([
                'batch_no' => "No batch '{$request->batch_no}' sudah ada (#{$dup->id}, qty {$dup->qty}). Tambahkan qty lewat Edit pada baris tersebut, bukan buat baris baru.",
            ])->withInput();
        }

        ProductBatch::create($request->all());

        return redirect()->back()->with('message', 'Batch berhasil ditambahkan');
    }

    public function update(Request $request)
    {
        $batch = ProductBatch::findOrFail($request->id);
        $request->merge(['expiry_date' => normalize_expiry_date($request->expiry_date)]);
        $request->merge(['buy_price' => self::parsePrice($request->input('buy_price'))]);
        
        $validator = Validator::make($request->all(), [
            'qty' => 'required|integer|min:0',
            'batch_no' => 'required|string',
            'expiry_date' => 'required|date',
            'buy_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Cegah edit no batch menjadi duplikat baris lain
        $dup = ProductBatch::findDuplicate(
            $batch->product_id,
            $batch->product_variant_id,
            $batch->warehouse_id,
            $request->batch_no,
            $batch->id
        );
        if ($dup) {
            return redirect()->back()->withErrors([
                'batch_no' => "No batch '{$request->batch_no}' sudah dipakai baris #{$dup->id}. Pakai Merge Batch di Adjustment untuk menggabungkannya.",
            ])->withInput();
        }

        $batch->update($request->only(['qty', 'batch_no', 'expiry_date', 'buy_price']));

        return redirect()->back()->with('message', 'Data batch berhasil diperbarui');
    }

    /**
     * Parse harga format Indonesia ("48.729", "48729,00") ke integer.
     * Titik = pemisah ribuan (dibuang), koma = desimal (dibuang).
     */
    private static function parsePrice($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $v = str_replace('.', '', (string) $value);
        $v = preg_replace('/,.*$/', '', $v);
        $v = preg_replace('/[^0-9]/', '', $v);

        return $v === '' ? null : (int) $v;
    }

    public function delete(Request $request)
    {
        $batch = ProductBatch::findOrFail($request->id);
        $batch->delete();

        return response()->json(['message' => 'Batch berhasil dihapus'], 200);
    }
}
