<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index()
    {
        return view('admin.customers.index')->with('sb', 'Customers');
    }

    public function getall(Request $request)
    {
        // Aggregate customers from transactions table
        $query = Transaction::select(
            'customer_phone',
            DB::raw('MAX(customer_name) as customer_name'),
            DB::raw('MAX(customer_email) as customer_email'),
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('SUM(total_amount) as total_spending'),
            DB::raw('MAX(created_at) as last_transaction')
        )
        ->whereNotNull('customer_phone')
        ->groupBy('customer_phone');

        // Apply filters
        if ($request->filter == 'frequent') {
            $query->orderBy('total_transactions', 'desc');
        } elseif ($request->filter == 'newest') {
            $query->orderBy('last_transaction', 'desc');
        } elseif ($request->filter == 'inactive') {
            $query->having('last_transaction', '<', now()->subDays(30));
        } else {
            $query->orderBy('last_transaction', 'desc');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('total_spending_formatted', function($row) {
                return 'Rp ' . number_format($row->total_spending);
            })
            ->addColumn('last_transaction_formatted', function($row) {
                return $row->last_transaction ? \Carbon\Carbon::parse($row->last_transaction)->format('d/m/Y H:i') : '-';
            })
            ->addColumn('status', function($row) {
                $lastTrx = \Carbon\Carbon::parse($row->last_transaction);
                $daysSinceLast = now()->diffInDays($lastTrx);
                if ($daysSinceLast > 60) {
                    return '<span class="badge badge-danger">Tidak Aktif</span>';
                }
                
                if ($row->total_transactions > 10) {
                    return '<span class="badge badge-primary">Loyal</span>';
                } elseif ($row->total_transactions > 3) {
                    return '<span class="badge badge-success">Potensial</span>';
                } else {
                    return '<span class="badge badge-info">Aktif</span>';
                }
            })
            ->addColumn('action', function($row) {
                return '<a href="'.route('admin.customers.show', $row->customer_phone).'" class="btn btn-sm btn-info">Detail</a>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function show($phone)
    {
        $customer = Transaction::where('customer_phone', $phone)
            ->select('customer_name', 'customer_phone', 'customer_email', 
                    DB::raw('COUNT(*) as total_transactions'),
                    DB::raw('SUM(total_amount) as total_spending'),
                    DB::raw('AVG(total_amount) as avg_transaction'),
                    DB::raw('MAX(created_at) as last_transaction'))
            ->groupBy('customer_phone', 'customer_name', 'customer_email')
            ->firstOrFail();

        // Transaction History
        $transactions = Transaction::where('customer_phone', $phone)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Favorite Products
        $favoriteProducts = TransactionItem::whereHas('transaction', function($q) use ($phone) {
                $q->where('customer_phone', $phone);
            })
            ->select('product_id', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_value'))
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        return view('admin.customers.show', compact('customer', 'transactions', 'favoriteProducts'))->with('sb', 'Customers');
    }
}
