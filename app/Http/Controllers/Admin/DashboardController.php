<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\LogAktivitas;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $data = [
            'today'            => Carbon::createFromFormat('Y-m-d', date('Y-m-d'))->isoFormat('dddd, D MMMM Y'),
            'userCount'        => User::count(),
            'productCount'     => Product::count(),
            'transactionToday' => Transaction::whereDate('created_at', $today)->where('payment_status', 'paid')->count(),
            'incomeToday'      => Transaction::whereDate('created_at', $today)->where('payment_status', 'paid')->sum('total_amount'),
            'latestProducts'   => Product::latest()->take(5)->get(),
            'lowStockProducts' => Product::whereColumn('stock', '<=', 'min_stock_alert')->where('status', 'Y')->get(),
        ];

        return view('admin.dashboard.index', $data)->with('sb', 'Dashboard');
    }
}
