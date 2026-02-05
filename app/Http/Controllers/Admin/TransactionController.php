<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index()
    {
        return view('admin.transaction.index')->with([
            'sb' => 'Transaction'
        ]);
    }

    public function getall(Request $request)
    {
        $query = Transaction::select(
            'transactions.id',
            'transactions.user_id',
            'transactions.total_amount',
            'transactions.payment_status',
            'transactions.delivery_type',
            'transactions.created_at',
            'users.name as user_name'
        )
        ->join('users', 'transactions.user_id', '=', 'users.id')->orderBy('id', 'desc');

        // Apply filters
        if ($request->has('delivery_type') && !empty($request->delivery_type)) {
            $query->where('transactions.delivery_type', $request->delivery_type);
        }

        if ($request->has('payment_status') && !empty($request->payment_status)) {
            $query->where('transactions.payment_status', $request->payment_status);
        }

        if ($request->has('start_date') && !empty($request->start_date)) {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $query->where('transactions.created_at', '>=', $startDate);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            $query->where('transactions.created_at', '<=', $endDate);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('user.name', function ($transaction) {
                return $transaction->user_name;
            })
            ->addColumn('action', function ($transaction) {
                return '<a href="' . url('admin/transactions/show/'. $transaction->id) . '" class="btn btn-sm btn-primary">Detail</a>';
            })
            ->editColumn('created_at', function ($transaction) {
                return Carbon::parse($transaction->created_at)->format('d-m-Y H:i');
            })
            ->editColumn('total_amount', function ($transaction) {
                return 'Rp ' . number_format($transaction->total_amount, 0, ',', '.');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['user', 'items.product', 'items.batch'])->findOrFail($id);
        return view('admin.transaction.show', compact('transaction'))->with('sb', 'Transaction');
    }

    public function print(Request $request)
    {
        $query = Transaction::select(
            'transactions.id',
            'transactions.user_id',
            'transactions.total_amount',
            'transactions.payment_status',
            'transactions.delivery_type',
            'transactions.created_at',
            'users.name as user_name'
        )
        ->join('users', 'transactions.user_id', '=', 'users.id');

        // Apply filters
        if ($request->has('delivery_type') && !empty($request->delivery_type)) {
            $query->where('transactions.delivery_type', $request->delivery_type);
        }

        if ($request->has('payment_status') && !empty($request->payment_status)) {
            $query->where('transactions.payment_status', $request->payment_status);
        }

        if ($request->has('start_date') && !empty($request->start_date)) {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $query->where('transactions.created_at', '>=', $startDate);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            $query->where('transactions.created_at', '<=', $endDate);
        }

        $transactions = $query->orderBy('id', 'desc')->get();

        return view('admin.transaction.print', compact('transactions'));
    }
}