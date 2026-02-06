<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AttributeGroupController extends Controller
{
    public function index()
    {
        return view('admin.manage_master.attribute_groups.index')->with('sb', 'Attribute Groups');
    }

    public function getall()
    {
        $data = AttributeGroup::orderBy('id', 'desc')->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                return '
                    <button class="btn btn-sm btn-warning edit" data-id="'.$row->id.'">Edit</button>
                    <button class="btn btn-sm btn-danger delete" data-id="'.$row->id.'">Hapus</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:attribute_groups,code',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        AttributeGroup::create([
            'name' => $request->name,
            'code' => strtoupper($request->code)
        ]);

        return response()->json(['status' => true, 'message' => 'Grup Atribut berhasil disimpan']);
    }

    public function get(Request $request)
    {
        $data = AttributeGroup::find($request->id);
        return response()->json($data);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:attribute_groups,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:attribute_groups,code,'.$request->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $group = AttributeGroup::find($request->id);
        $group->update([
            'name' => $request->name,
            'code' => strtoupper($request->code)
        ]);

        return response()->json(['status' => true, 'message' => 'Grup Atribut berhasil diupdate']);
    }

    public function delete(Request $request)
    {
        $group = AttributeGroup::find($request->id);
        if ($group) {
            $group->delete();
            return response()->json(['status' => true, 'message' => 'Grup Atribut berhasil dihapus']);
        }
        return response()->json(['status' => false, 'message' => 'Data tidak ditemukan']);
    }
}
