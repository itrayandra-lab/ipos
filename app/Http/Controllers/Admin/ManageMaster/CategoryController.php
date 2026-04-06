<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.manage_master.categories.index')->with(['sb' => 'Categories']);
    }

    public function getall(Request $request)
    {
        $data = Category::orderBy('name', 'ASC')->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('code', function ($row) {
                return $row->code ?? '-';
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
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:10',
            'description' => 'nullable|string',
        ]);

        try {
            Category::create($request->all());
            return redirect()->back()->with('message', 'Kategori berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan kategori: ' . $e->getMessage());
        }
    }

    public function get(Request $request)
    {
        return response()->json(Category::find($request->id));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:10',
            'description' => 'nullable|string',
        ]);

        try {
            Category::find($request->id)->update($request->all());
            return redirect()->back()->with('message', 'Kategori berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui kategori: ' . $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        Category::find($request->id)->delete();
        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }

    public function getSubCategories(Request $request)
    {
        $id = $request->id;
        $subs = SubCategory::where('category_id', $id)->orderBy('name', 'ASC')->get();
        return response()->json($subs);
    }
}
