<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\ProductType;
use Yajra\DataTables\Facades\DataTables;

class ProductTypeController extends Controller
{
    public function index()
    {
        return view('admin.manage_master.product_types.index')->with([
            'sb' => 'ProductTypes'
        ]);
    }

    public function getall(Request $request)
    {
        $data = ProductType::orderBy('id', 'desc')->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
            return '
                    <div class="dropdown d-inline dropleft">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                            Action
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item edit" href="javascript:void(0)" data-id="' . $row->id . '">Edit</a></li>
                            <li><a class="dropdown-item hapus" href="javascript:void(0)" data-id="' . $row->id . '">Hapus</a></li>
                        </ul>
                    </div>
                ';
        })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(Request $request)
    {
        ProductType::create($request->all());
        return redirect()->back()->with('message', 'Tipe Produk berhasil ditambahkan');
    }

    public function get(Request $request)
    {
        return response()->json(ProductType::find($request->id));
    }

    public function update(Request $request)
    {
        ProductType::find($request->id)->update($request->all());
        return redirect()->back()->with('message', 'Tipe Produk berhasil diperbarui');
    }

    public function delete(Request $request)
    {
        ProductType::find($request->id)->delete();
        return response()->json(['message' => 'Tipe Produk berhasil dihapus']);
    }
}
