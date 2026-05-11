<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class SettlementController extends Controller
{
    public function index()
    {
        return view('admin.finance.settlement_report')->with([
            'sb' => 'FinanceSettlement'
        ]);
    }

    public function data(Request $request)
    {
        $query = DB::table('transaction_items')
            ->select(
                'merek.name as merek_name',
                'products.name as product_name',
                'product_variants.variant_name',
                'product_variants.sku_code',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.qty * transaction_items.buy_price) as total_cost')
            )
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->leftJoin('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id');

        // Filter by date
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('transactions.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('transactions.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        // Bundling Logic: Show components, hide bundle parents
        $query->where(function($q) {
            $q->where('products.is_bundle', 0)
              ->orWhereNotNull('transaction_items.parent_item_id');
        });

        $query->groupBy(
            'transaction_items.product_id', 
            'transaction_items.product_variant_id', 
            'merek.name', 
            'products.name', 
            'product_variants.variant_name', 
            'product_variants.sku_code'
        )
        ->orderBy('total_qty', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('total_cost', function ($row) {
                return 'Rp ' . number_format($row->total_cost, 0, ',', '.');
            })
            ->make(true);
    }
}
