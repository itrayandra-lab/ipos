<?php

namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index()
    {
        return view('admin.purchasing.suppliers.index')->with('sb', 'Supplier');
    }

    public function getall()
    {
        $suppliers = Supplier::query();

        return DataTables::of($suppliers)
            ->addIndexColumn()
            ->addColumn('action', function ($supplier) {
            return '
                    <div class="dropdown d-inline dropleft">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true" data-toggle="dropdown">
                            Action
                        </button>
                        <ul class="dropdown-menu">
                            <li><a data-id="' . $supplier->id . '" class="dropdown-item btn-edit" href="#">Edit</a></li>
                            <li><a data-id="' . $supplier->id . '" class="dropdown-item btn-delete" href="#">Hapus</a></li>
                        </ul>
                    </div>';
        })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'tax_status' => 'required|in:PKP,Non-PKP',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $data = $request->all();
        $data['code'] = Supplier::generateCode();
        $data['created_by'] = Auth::id();

        Supplier::create($data);

        return response()->json(['status' => 'success', 'message' => 'Supplier berhasil ditambahkan']);
    }

    public function get(Request $request)
    {
        $supplier = Supplier::findOrFail($request->id);
        return response()->json($supplier);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:suppliers,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'tax_status' => 'required|in:PKP,Non-PKP',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $supplier = Supplier::findOrFail($request->id);
        $supplier->update($request->all());

        return response()->json(['status' => 'success', 'message' => 'Supplier berhasil diperbarui']);
    }

    public function delete(Request $request)
    {
        $supplier = Supplier::findOrFail($request->id);

        // Cek apakah supplier sudah digunakan di PO
        if ($supplier->purchaseOrders()->count() > 0) {
            return response()->json(['status' => 'error', 'message' => 'Supplier tidak dapat dihapus karena memiliki riwayat Order Pembelian'], 422);
        }

        $supplier->delete();

        return response()->json(['status' => 'success', 'message' => 'Supplier berhasil dihapus']);
    }
}
