<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfMonth();
        $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();

        $user = auth()->user();
        $warehouseIds = null;
        if (!$user->isSuperAdmin() && !$user->isStoreManager()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();
        }

        $baseQuery = Transaction::where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$start, $end]);

        if ($warehouseIds !== null) {
            $baseQuery->whereIn('warehouse_id', $warehouseIds);
        }

        $grossItemsQuery = TransactionItem::leftJoin('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->whereHas('transaction', function ($q) use ($start, $end, $warehouseIds) {
                $q->where('payment_status', 'paid')->whereBetween('transaction_date', [$start, $end]);
                if ($warehouseIds !== null) $q->whereIn('warehouse_id', $warehouseIds);
            });

        $grossSales = (clone $grossItemsQuery)->select(DB::raw('COALESCE(SUM(transaction_items.price * transaction_items.qty), 0) as total'))->first()->total;

        $cogs = (clone $grossItemsQuery)->select(DB::raw('COALESCE(SUM(transaction_items.qty * COALESCE(NULLIF(transaction_items.buy_price, 0), product_variants.product_hpp, 0)), 0) as total'))->first()->total;

        $itemDiscounts = (clone $grossItemsQuery)->select(DB::raw('COALESCE(SUM(COALESCE(transaction_items.discount, 0)), 0) as total'))->first()->total;

        $txDiscount = (clone $baseQuery)->select(DB::raw('COALESCE(SUM(CAST(COALESCE(discount, 0) AS DECIMAL(15,2))), 0) as total'))->first()->total;

        $totalDiscount = $txDiscount + $itemDiscounts;
        $netSales = $grossSales - $totalDiscount;
        $grossProfit = $netSales - $cogs;

        $channelSummary = (clone $baseQuery)
            ->select('source', DB::raw('COUNT(*) as total_tx'), DB::raw('COALESCE(SUM(CAST(total_amount AS DECIMAL(15,2))), 0) as total_revenue'))
            ->groupBy('source')
            ->orderByDesc('total_revenue')
            ->get();

        $dailySummary = (clone $baseQuery)
            ->select(DB::raw('transaction_date as date'), DB::raw('COUNT(*) as total_tx'), DB::raw('COALESCE(SUM(CAST(total_amount AS DECIMAL(15,2))), 0) as total_revenue'))
            ->groupBy('transaction_date')
            ->orderBy('date')
            ->get();

        $warehouses = collect();
        if ($user->isSuperAdmin() || $user->isStoreManager()) {
            $warehouses = Warehouse::orderBy('name')->get();
        } else {
            $warehouses = $user->warehouses()->orderBy('name')->get();
        }

        return view('admin.finance.reports.index', compact(
            'start', 'end',
            'grossSales', 'totalDiscount', 'netSales', 'cogs', 'grossProfit',
            'channelSummary', 'dailySummary', 'warehouses'
        ))->with('sb', 'FinanceReport');
    }
}
