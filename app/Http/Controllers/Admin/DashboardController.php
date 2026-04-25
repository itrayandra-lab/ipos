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
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Sales Chart Data (Last 30 Days)
        $salesData = Transaction::where('payment_status', 'paid')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // Top Selling Products
        $topProducts = \App\Models\TransactionItem::with(['product.merek'])
            ->selectRaw('product_id, SUM(qty) as total_qty')
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->take(5)
            ->get();

        // Payment Method Distribution
        $paymentMethods = Transaction::where('payment_status', 'paid')
            ->selectRaw('payment_method, COUNT(*) as total')
            ->groupBy('payment_method')
            ->get();

        $data = [
            'today'            => Carbon::now()->isoFormat('dddd, D MMMM Y'),
            'userCount'        => User::count(),
            'productCount'     => Product::count(),
            'transactionToday' => Transaction::whereDate('created_at', $today)->count(),
            'incomeToday'      => Transaction::whereDate('created_at', $today)->where('payment_status', 'paid')->sum('total_amount'),
            'incomeMonthly'    => Transaction::whereMonth('created_at', Carbon::now()->month)->where('payment_status', 'paid')->sum('total_amount'),
            'latestProducts'   => Product::latest()->take(5)->get(),
            'counts'           => Transaction::select('payment_status', \DB::raw('count(*) as total'))
                                    ->groupBy('payment_status')
                                    ->get()
                                    ->pluck('total', 'payment_status')
                                    ->toArray(),
            'salesChart'       => [
                'labels' => $salesData->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray(),
                'totals' => $salesData->pluck('total')->toArray(),
            ],
            'topProducts'      => $topProducts,
            'paymentMethods'   => [
                'labels' => $paymentMethods->pluck('payment_method')->map(fn($m) => strtoupper($m))->toArray(),
                'totals' => $paymentMethods->pluck('total')->toArray(),
            ],
            'avgOrderValue'    => Transaction::where('payment_status', 'paid')->avg('total_amount') ?? 0,
        ];

        return view('admin.dashboard.index', $data)->with('sb', 'Dashboard');
    }
}
