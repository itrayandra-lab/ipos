<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Merek;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class MerekController extends Controller
{
    public function index()
    {
        return view('admin.manage_master.merek.index')->with('sb', 'Merek');
    }

    public function getall(Request $request)
    {
        $query = Merek::select('id', 'name', 'code', 'slug', 'description')
            ->orderBy('name', 'ASC');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function (Merek $merek) {
                return '
                <div class="dropdown d-inline">
                    <button class="btn btn-primary dropdown-toggle btn-sm" type="button" data-toggle="dropdown">
                        Aksi
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item has-icon edit" href="#" data-id="' . $merek->id . '">
                            <i class="fas fa-edit text-primary"></i> Edit
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item has-icon hapus text-danger" href="#" data-id="' . $merek->id . '">
                            <i class="fas fa-trash"></i> Hapus
                        </a>
                    </div>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:merek,code',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Merek::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return redirect()->back()->with('message', 'Data merek berhasil disimpan');
    }

    public function get(Request $request)
    {
        return response()->json(
            Merek::findOrFail($request->id),
            200
        );
    }


    public function update(Request $request)
    {
        $id = $request->id;
        if (!$id) {
            return redirect()->back()->with('error', 'ID merek tidak ditemukan');
        }

        $merek = Merek::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:merek,code,' . $id,
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $slug = Str::slug($request->name);
        $count = Merek::where('slug', $slug)
            ->where('id', '!=', $id)
            ->count();

        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $merek->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('message', 'Data merek berhasil diupdate');
    }


    public function delete(Request $request)
    {
        $merek = Merek::findOrFail($request->id);
        $merek->delete();
        return response()->json(['message' => 'Data merek berhasil dihapus'], 200);
    }
}
