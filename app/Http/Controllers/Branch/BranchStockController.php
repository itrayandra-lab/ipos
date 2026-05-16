<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\ProductBatch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class BranchStockController extends Controller
{
    public function index()
    {
        $warehouse = Auth::user()->warehouse;
        return view('branch.stock.index', compact('warehouse'))->with('sb', 'BranchStock');
    }

    public function getall()
    {
        $user      = Auth::user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return response()->json(['data' => []]);
        }

        $batches = ProductBatch::with(['product.merek', 'variant'])
            ->where('warehouse_id', $warehouse->id)
            ->where('qty', '>', 0)
            ->orderBy('created_at', 'desc');

        return DataTables::of($batches)
            ->addIndexColumn()
            ->addColumn('product_name', fn($b) => ($b->product->merek->name ?? '') . ' ' . ($b->product->name ?? '-'))
            ->addColumn('variant_name', fn($b) => $b->variant->variant_name ?? '-')
            ->editColumn('qty', fn($b) => number_format($b->qty, 0, ',', '.'))
            ->editColumn('expiry_date', fn($b) => $b->expiry_date ? $b->expiry_date->format('d/m/Y') : '-')
            ->editColumn('buy_price', fn($b) => 'Rp ' . number_format($b->buy_price, 0, ',', '.'))
            ->rawColumns([])
            ->make(true);
    }
}
