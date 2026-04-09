<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductTier;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ProductTierController extends Controller
{
    public function index()
    {
        return view('admin.manage_master.product_tiers.index')->with('sb', 'ProductTier');
    }

    public function getall(Request $request)
    {
        $query = ProductTier::select('id', 'name', 'multiplier')
            ->orderBy('name', 'ASC')
            ->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('multiplier_display', function (ProductTier $tier) {
                return 'x' . $tier->multiplier;
            })
            ->addColumn('action', function (ProductTier $tier) {
                return '
                    <div class="dropdown d-inline dropleft">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true" data-toggle="dropdown">
                            Action
                        </button>
                        <ul class="dropdown-menu">
                            <li><a data-id="' . $tier->id . '" class="dropdown-item edit">Edit</a></li>
                            <li><a data-id="' . $tier->id . '" class="dropdown-item hapus" href="#">Hapus</a></li>
                        </ul>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:product_tiers,name',
            'multiplier' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        ProductTier::create([
            'name' => $request->name,
            'multiplier' => $request->multiplier,
        ]);

        return redirect()->back()->with('message', 'Data Tier berhasil disimpan');
    }

    public function get(Request $request)
    {
        return response()->json(
            ProductTier::findOrFail($request->id),
            200
        );
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $tier = ProductTier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:product_tiers,name,' . $id,
            'multiplier' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tier->update([
            'name' => $request->name,
            'multiplier' => $request->multiplier,
        ]);

        return redirect()->back()->with('message', 'Data Tier berhasil diupdate');
    }

    public function delete(Request $request)
    {
        $tier = ProductTier::findOrFail($request->id);
        $tier->delete();
        return response()->json(['message' => 'Data Tier berhasil dihapus'], 200);
    }
}
