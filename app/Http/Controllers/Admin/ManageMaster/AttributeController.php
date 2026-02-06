<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AttributeController extends Controller
{
    public function index()
    {
        $groups = AttributeGroup::orderBy('name')->get();
        return view('admin.manage_master.attributes.index', compact('groups'))->with('sb', 'Attributes');
    }

    public function getall()
    {
        $data = Attribute::with('group')->orderBy('id', 'desc')->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('group_name', function($row){
                return $row->group ? $row->group->name : '-';
            })
            ->addColumn('action', function($row){
                return '
                    <button class="btn btn-sm btn-warning edit" data-id="'.$row->id.'">Edit</button>
                    <button class="btn btn-sm btn-danger delete" data-id="'.$row->id.'">Hapus</button>
                ';
            })
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'attribute_group_id' => 'required|exists:attribute_groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        Attribute::create($request->all());

        return response()->json(['status' => true, 'message' => 'Atribut berhasil disimpan']);
    }

    public function get(Request $request)
    {
        $data = Attribute::find($request->id);
        return response()->json($data);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:attributes,id',
            'name' => 'required|string|max:255',
            'attribute_group_id' => 'required|exists:attribute_groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $attribute = Attribute::find($request->id);
        $attribute->update($request->all());

        return response()->json(['status' => true, 'message' => 'Atribut berhasil diupdate']);
    }

    public function delete(Request $request)
    {
        $attribute = Attribute::find($request->id);
        if ($attribute) {
            $attribute->delete();
            return response()->json(['status' => true, 'message' => 'Atribut berhasil dihapus']);
        }
        return response()->json(['status' => false, 'message' => 'Data tidak ditemukan']);
    }
}
