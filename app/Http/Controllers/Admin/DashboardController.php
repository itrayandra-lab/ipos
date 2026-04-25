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
    public function index(Request $request)
    {
        $today = Carbon::today();
        $range = $request->get('range', 'month');
        
        switch ($range) {
            case 'week':
                $startDate = Carbon::now()->subWeek();
                $days = 7;
                break;
            case 'year':
                $startDate = Carbon::now()->subYear();
                $days = 365; // We'll group by month for year
                break;
            case 'month':
            default:
                $startDate = Carbon::now()->subMonth();
                $days = 30;
                break;
        }

        // Sales Chart Data
        $salesData = Transaction::where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // Platform Transaction Count Chart (Multi-line)
        $platforms = \App\Models\ChannelSetting::pluck('name', 'slug')->toArray();
        $rawPlatformData = Transaction::where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->whereIn('source', array_keys($platforms))
            ->selectRaw('source, DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('source', 'date')
            ->get()
            ->groupBy('source');
        
        $labels = [];
        if ($range == 'year') {
            // Group by Month for year view
            for ($i = 11; $i >= 0; $i--) {
                $labels[] = Carbon::now()->subMonths($i)->format('M Y');
            }
        } else {
            for ($i = $days - 1; $i >= 0; $i--) {
                $labels[] = Carbon::now()->subDays($i)->format('d M');
            }
        }

        $platformDatasets = [];
        $colors = ['#0d9488', '#0ea5e9', '#8b5cf6', '#f59e0b', '#ef4444', '#10b981', '#6366f1'];
        $colorIdx = 0;

        foreach ($platforms as $slug => $name) {
            $dataPoints = [];
            $platformData = $rawPlatformData->get($slug, collect());
            
            if ($range == 'year') {
                $platformDataByMonth = $platformData->groupBy(function($d) {
                    return Carbon::parse($d->date)->format('M Y');
                });
                for ($i = 11; $i >= 0; $i--) {
                    $monthLabel = Carbon::now()->subMonths($i)->format('M Y');
                    $dataPoints[] = $platformDataByMonth->has($monthLabel) ? $platformDataByMonth->get($monthLabel)->sum('count') : 0;
                }
            } else {
                $platformDataByDate = $platformData->keyBy('date');
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i)->format('Y-m-d');
                    $dataPoints[] = $platformDataByDate->has($date) ? (int)$platformDataByDate->get($date)->count : 0;
                }
            }
            
            $platformDatasets[] = [
                'label' => $name,
                'data' => $dataPoints,
                'borderColor' => $colors[$colorIdx % count($colors)],
                'backgroundColor' => 'transparent',
                'borderWidth' => 3,
                'tension' => 0.4,
                'pointRadius' => 3
            ];
            $colorIdx++;
        }

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
            'platformChart'    => [
                'labels' => $labels,
                'datasets' => $platformDatasets,
            ],
            'topProducts'      => $topProducts,
            'paymentMethods'   => [
                'labels' => $paymentMethods->pluck('payment_method')->map(fn($m) => strtoupper($m))->toArray(),
                'totals' => $paymentMethods->pluck('total')->toArray(),
            ],
            'avgOrderValue'    => Transaction::where('payment_status', 'paid')->avg('total_amount') ?? 0,
            'currentRange'     => $range
        ];

        return view('admin.dashboard.index', $data)->with('sb', 'Dashboard');
    }
}
