<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BranchProductController extends Controller
{
    public function index()
    {
        return view('branch.products.index')->with('sb', 'ProductCatalog');
    }

    public function getall(Request $request)
    {
        $query = Product::with(['merek', 'subCategory', 'variants'])
            ->select('products.*')
            ->orderBy('name', 'ASC');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('merek_name', fn($p) => $p->merek?->name ?? '-')
            ->addColumn('category_name', fn($p) => $p->subCategory?->name ?? '-')
            ->addColumn('prices', function($p) {
                if ($p->variants->isEmpty()) {
                    return 'Rp ' . number_format($p->price_real > 0 ? $p->price_real : $p->price, 0, ',', '.');
                }
                return $p->variants->map(function($v) {
                    $price = method_exists($v, 'getSellingPrice') ? $v->getSellingPrice() : $v->price;
                    return ($v->variant_name ?: 'Default') . ': Rp ' . number_format($price, 0, ',', '.');
                })->implode('<br>');
            })
            ->rawColumns(['prices'])
            ->make(true);
    }
}
