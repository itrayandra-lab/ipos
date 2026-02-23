<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use Yajra\DataTables\Facades\DataTables;

class SubCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name', 'ASC')->get();
        return view('admin.manage_master.sub_categories.index')->with([
            'sb' => 'Sub Categories',
            'categories' => $categories
        ]);
    }

    public function getall(Request $request)
    {
        $data = SubCategory::with('category')->orderBy('name', 'ASC')->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('category_name', function ($row) {
            return $row->category->name ?? '-';
        })
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
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
        ]);

        SubCategory::create($request->all());
        return redirect()->back()->with('message', 'Sub Kategori berhasil ditambahkan');
    }

    public function get(Request $request)
    {
        return response()->json(SubCategory::find($request->id));
    }

    public function update(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
        ]);

        SubCategory::find($request->id)->update($request->all());
        return redirect()->back()->with('message', 'Sub Kategori berhasil diperbarui');
    }

    public function delete(Request $request)
    {
        SubCategory::find($request->id)->delete();
        return response()->json(['message' => 'Sub Kategori berhasil dihapus']);
    }
}
