<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $warehouseId = $request->warehouse_id;
        $warehouse = null;

        if ($user->isSales()) {
            $salesWarehouse = $user->warehouses->first();
            if ($salesWarehouse) {
                $warehouseId = $salesWarehouse->id;
                $warehouse = $salesWarehouse;
            }
        } elseif ($warehouseId) {
            $warehouse = \App\Models\Warehouse::find($warehouseId);
        }

        $today = Carbon::today();
        $now = Carbon::now();
        $range = $request->get('range', 'month');
        $startDate = null;
        $endDate = $now->copy()->endOfDay();
        
        switch ($range) {
            case 'custom':
                if ($request->has('start_date') && !empty($request->start_date)) {
                    $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
                }
                if ($request->has('end_date') && !empty($request->end_date)) {
                    $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
                }
                if (!$startDate) $startDate = Carbon::now()->subMonth()->startOfDay();
                $days = $startDate->diffInDays($endDate) + 1;
                break;
            case 'week':
                $startDate = $now->copy()->startOfWeek()->startOfDay();
                $days = $now->dayOfWeekIso;
                break;
            case 'year':
                $startDate = $now->copy()->startOfYear()->startOfDay();
                $days = 365;
                break;
            case 'month':
            default:
                $startDate = $now->copy()->startOfMonth()->startOfDay();
                $days = $now->day;
                break;
        }

        // Base Query for Transactions
        $baseTransaction = Transaction::query()
            ->when($warehouseId, function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });

        // Sales Chart Data
        $rawSales = (clone $baseTransaction)->where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('transaction_date as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->keyBy('date');

        $salesData = collect();
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $dateStr = $cursor->format('Y-m-d');
            $salesData->push((object)[
                'date'  => $dateStr,
                'total' => $rawSales->has($dateStr) ? (float)$rawSales->get($dateStr)->total : 0,
            ]);
            $cursor->addDay();
        }

        // Platform Transaction Count Chart (Multi-line)
        $platforms = \App\Models\ChannelSetting::pluck('name', 'slug')->toArray();
        $rawPlatformData = (clone $baseTransaction)->where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->whereIn('source', array_keys($platforms))
            ->selectRaw('source, transaction_date as date, COUNT(*) as count')
            ->groupBy('source', 'date')
            ->get()
            ->groupBy('source');
        
        $labels = [];
        if ($range == 'year') {
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = Carbon::createFromDate($now->year, $m, 1)->format('M Y');
            }
        } else {
            for ($i = $days - 1; $i >= 0; $i--) {
                $labels[] = $endDate->copy()->subDays($i)->format('d M');
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
                for ($m = 1; $m <= 12; $m++) {
                    $monthLabel = Carbon::createFromDate($now->year, $m, 1)->format('M Y');
                    $dataPoints[] = $platformDataByMonth->has($monthLabel) ? $platformDataByMonth->get($monthLabel)->sum('count') : 0;
                }
            } else {
                $platformDataByDate = $platformData->keyBy('date');
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = $endDate->copy()->subDays($i)->format('Y-m-d');
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
            ->whereHas('transaction', function($q) use ($startDate, $endDate, $warehouseId) {
                $q->whereBetween('transaction_date', [$startDate, $endDate])
                  ->when($warehouseId, function($sq) use ($warehouseId) {
                      $sq->where('warehouse_id', $warehouseId);
                  });
            })
            ->selectRaw('product_id, SUM(qty) as total_qty')
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->take(5)
            ->get();

        // Payment Method Distribution
        $paymentMethods = (clone $baseTransaction)->where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('payment_method, COUNT(*) as total')
            ->groupBy('payment_method')
            ->get();

        $warehouses = \App\Models\Warehouse::orderBy('name')->get();

        $data = [
            'today'            => Carbon::now()->isoFormat('dddd, D MMMM Y'),
            'userCount'        => User::count(),
            'productCount'     => Product::count(),
            'transactionToday' => (clone $baseTransaction)->whereBetween('transaction_date', [$startDate, $endDate])->count(),
            'incomeToday'      => (clone $baseTransaction)->whereDate('transaction_date', $today)->where('payment_status', 'paid')->sum('total_amount'),
            'incomeMonthly'    => (clone $baseTransaction)->whereBetween('transaction_date', [$now->copy()->startOfMonth(), $endDate])->where('payment_status', 'paid')->sum('total_amount'),
            'incomeSelected'   => (clone $baseTransaction)->whereBetween('transaction_date', [$startDate, $endDate])->where('payment_status', 'paid')->sum('total_amount'),
            'latestProducts'   => Product::latest()->take(5)->get(),
            'counts'           => (clone $baseTransaction)->whereBetween('transaction_date', [$startDate, $endDate])
                                    ->select('payment_status', \DB::raw('count(*) as total'))
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
            'avgOrderValue'    => (clone $baseTransaction)->whereBetween('transaction_date', [$startDate, $endDate])->where('payment_status', 'paid')->avg('total_amount') ?? 0,
            'currentRange'     => $range,
            'startDate'        => $startDate->format('Y-m-d'),
            'endDate'          => $endDate->format('Y-m-d'),
            'warehouses'       => $warehouses,
            'selectedWarehouse'=> $warehouse
        ];

        return view('admin.dashboard.index', $data)->with('sb', 'Dashboard');
    }
}
