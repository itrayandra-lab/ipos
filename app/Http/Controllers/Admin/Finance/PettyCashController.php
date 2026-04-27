<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\PettyCashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PettyCashController extends Controller
{
    public function index()
    {
        $latest = PettyCashTransaction::latest()->first();
        $balance = $latest ? $latest->balance_after : 0;
        return view('admin.finance.petty_cash.index', compact('balance'))->with('sb', 'PettyCash');
    }

    public function data()
    {
        $query = PettyCashTransaction::with('user')->orderBy('id', 'desc');
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('type', function ($row) {
                return $row->type == 'in' 
                    ? '<span class="badge badge-success">Masuk</span>' 
                    : '<span class="badge badge-danger">Keluar</span>';
            })
            ->editColumn('amount', function ($row) {
                return 'Rp ' . number_format($row->amount, 0, ',', '.');
            })
            ->editColumn('balance_after', function ($row) {
                return 'Rp ' . number_format($row->balance_after, 0, ',', '.');
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('d-m-Y H:i');
            })
            ->rawColumns(['type'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $latest = PettyCashTransaction::latest()->first();
                $lastBalance = $latest ? $latest->balance_after : 0;
                
                if ($request->type == 'out' && $lastBalance < $request->amount) {
                    throw new \Exception('Saldo kas kecil tidak mencukupi.');
                }

                $newBalance = $request->type == 'in' 
                    ? $lastBalance + $request->amount 
                    : $lastBalance - $request->amount;

                PettyCashTransaction::create([
                    'type' => $request->type,
                    'amount' => $request->amount,
                    'description' => $request->description,
                    'balance_after' => $newBalance,
                    'user_id' => Auth::id(),
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Transaksi kas kecil berhasil dicatat.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
