<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('admin.inventory.warehouses.index')->with('sb', 'Warehouse');
    }

    public function getall()
    {
        $warehouses = Warehouse::orderBy('name', 'asc');

        return DataTables::of($warehouses)
            ->addIndexColumn()
            ->editColumn('status', function ($wh) {
                return $wh->status === 'active' 
                    ? '<span class="badge badge-success">Active</span>' 
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function ($wh) {
                return '
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-info btn-edit" data-id="' . $wh->id . '"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $wh->id . '"><i class="fas fa-trash"></i></button>
                </div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:main,branch',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        Warehouse::create($request->all());

        return response()->json(['status' => 'success', 'message' => 'Gudang berhasil ditambahkan']);
    }

    public function get(Request $request)
    {
        $wh = Warehouse::findOrFail($request->id);
        return response()->json(['status' => 'success', 'data' => $wh]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:main,branch',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $wh = Warehouse::findOrFail($request->id);
        $wh->update($request->all());

        return response()->json(['status' => 'success', 'message' => 'Gudang berhasil diperbarui']);
    }

    public function delete(Request $request)
    {
        $wh = Warehouse::findOrFail($request->id);
        // Check if has batches or orders
        if ($wh->productBatches()->count() > 0 || $wh->purchaseOrders()->count() > 0) {
            return response()->json(['status' => 'error', 'message' => 'Gudang tidak bisa dihapus karena memiliki data transaksi/stok.']);
        }
        
        $wh->delete();
        return response()->json(['status' => 'success', 'message' => 'Gudang berhasil dihapus']);
    }
}
