<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BankAccountController extends Controller
{
    public function index()
    {
        return view('admin.settings.bank_accounts.index')->with('sb', 'Settings');
    }

    public function getall(Request $request)
    {
        $query = BankAccount::orderBy('id', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('status', function($row) {
                return $row->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>';
            })
            ->addColumn('action', function($row) {
                return '
                    <div class="dropdown d-inline">
                        <button class="btn btn-primary dropdown-toggle btn-sm" type="button" data-toggle="dropdown">
                            Action
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item edit" href="javascript:void(0)" data-id="'.$row->id.'"><i class="fas fa-edit"></i> Edit</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger delete" href="javascript:void(0)" data-id="'.$row->id.'"><i class="fas fa-trash"></i> Hapus</a>
                        </div>
                    </div>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:100',
        ]);

        BankAccount::create($request->all());

        return response()->json(['success' => true, 'message' => 'Rekening bank berhasil ditambahkan']);
    }

    public function get(Request $request)
    {
        $bankAccount = BankAccount::findOrFail($request->id);
        return response()->json($bankAccount);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:bank_accounts,id',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:100',
        ]);

        $bankAccount = BankAccount::findOrFail($request->id);
        $bankAccount->update($request->all());

        return response()->json(['success' => true, 'message' => 'Rekening bank berhasil diperbarui']);
    }

    public function delete(Request $request)
    {
        $bankAccount = BankAccount::findOrFail($request->id);
        $bankAccount->delete();

        return response()->json(['success' => true, 'message' => 'Rekening bank berhasil dihapus']);
    }

    public function toggleActive(Request $request)
    {
        $bankAccount = BankAccount::findOrFail($request->id);
        $bankAccount->update(['is_active' => !$bankAccount->is_active]);

        return response()->json(['success' => true, 'message' => 'Status rekening berhasil diubah']);
    }
}
