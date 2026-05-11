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
        $suppliers = \App\Models\Supplier::select('id', 'name')->orderBy('name', 'ASC')->get();
        return view('admin.finance.settlement_report')->with([
            'sb' => 'FinanceSettlement',
            'suppliers' => $suppliers
        ]);
    }

    public function data(Request $request)
    {
        $query = $this->getFilteredQuery($request);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('product_name', function($row) {
                $merekName = trim($row->merek_name ?? '');
                $productName = trim($row->product_name ?? '');
                $variantName = trim($row->variant_name ?? '');

                $originalParts = array_filter([$merekName, $productName, $variantName]);
                $finalParts = [];
                foreach ($originalParts as $p1) {
                    $isSubPart = false;
                    foreach ($originalParts as $p2) {
                        if ($p1 !== $p2 && stripos($p2, $p1) !== false && strlen($p2) > strlen($p1)) {
                            $isSubPart = true;
                            break;
                        }
                    }
                    if (!$isSubPart) {
                        $finalParts[] = $p1;
                    }
                }
                $labelText = implode(' ', array_unique($finalParts));
                return '<div class="font-weight-600">' . $labelText . '</div>';
            })
            ->editColumn('buy_price', function ($row) {
                return 'Rp ' . number_format($row->buy_price, 0, ',', '.');
            })
            ->editColumn('total_cost', function ($row) {
                return 'Rp ' . number_format($row->total_cost, 0, ',', '.');
            })
            ->addColumn('action', function($row) {
                return '<button type="button" class="btn btn-info btn-sm btn-detail" 
                            data-product-id="'.$row->product_id.'" 
                            data-variant-id="'.$row->product_variant_id.'"
                            data-product-name="'.e($row->product_name).'"
                            data-variant-name="'.e($row->variant_name).'">
                            <i class="fas fa-eye"></i> Detail
                        </button>';
            })
            ->rawColumns(['product_name', 'action'])
            ->with('summary', $this->getSummary($request))
            ->make(true);
    }

    private function getSummary(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        
        // Clone the query to calculate totals without grouping/ordering of the main table
        // Actually, since getFilteredQuery already has SUMs and GroupBy, we need to wrap it.
        $totals = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query)
            ->select(
                DB::raw('SUM(total_qty) as grand_total_qty'),
                DB::raw('SUM(total_cost) as grand_total_cost')
            )
            ->first();

        $supplier = null;
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $supplier = \App\Models\Supplier::find($request->supplier_id);
        }

        return [
            'total_qty' => $totals->grand_total_qty ?? 0,
            'total_cost' => $totals->grand_total_cost ?? 0,
            'supplier' => $supplier
        ];
    }

    private function getFilteredQuery(Request $request)
    {
        $query = DB::table('transaction_items')
            ->select(
                'transaction_items.product_id',
                'transaction_items.product_variant_id',
                'transaction_items.buy_price',
                'merek.name as merek_name',
                'products.name as product_name',
                'suppliers.name as supplier_name',
                'product_variants.variant_name',
                'product_variants.sku_code',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.qty * transaction_items.buy_price) as total_cost')
            )
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('suppliers', 'products.supplier_id', '=', 'suppliers.id')
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

        // Filter by supplier
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $query->where('products.supplier_id', $request->supplier_id);
        }

        // Bundling Logic: Show components, hide bundle parents
        $query->where(function($q) {
            $q->where('products.is_bundle', 0)
              ->orWhereNotNull('transaction_items.parent_item_id');
        });

        return $query->groupBy(
            'transaction_items.product_id', 
            'transaction_items.product_variant_id', 
            'transaction_items.buy_price',
            'merek.name', 
            'products.name', 
            'suppliers.name',
            'product_variants.variant_name', 
            'product_variants.sku_code'
        )
        ->orderBy('total_qty', 'desc');
    }

    public function exportExcel(Request $request)
    {
        $items = $this->getFilteredQuery($request)->get();
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SettlementExport($items), 'Laporan-Pelunasan-Supplier-' . date('d-m-Y') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $items = $this->getFilteredQuery($request)->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.finance.settlement_pdf', compact('items'));
        return $pdf->download('Laporan-Pelunasan-Supplier-' . date('d-m-Y') . '.pdf');
    }

    public function detail(Request $request)
    {
        $productId = $request->product_id;
        $variantId = $request->variant_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $query = DB::table('transaction_items')
            ->select(
                'transactions.id',
                'transactions.transaction_code',
                'transactions.invoice_number',
                'transactions.created_at',
                'transaction_items.qty',
                'transaction_items.buy_price',
                'transaction_items.price',
                'transaction_items.subtotal',
                'transactions.source',
                'users.name as cashier_name'
            )
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->where('transaction_items.product_id', $productId);

        if ($variantId && $variantId != 'null') {
            $query->where('transaction_items.product_variant_id', $variantId);
        } else {
            $query->whereNull('transaction_items.product_variant_id');
        }

        if ($startDate) {
            $query->where('transactions.created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate) {
            $query->where('transactions.created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $details = $query->orderBy('transactions.created_at', 'desc')->get();

        return response()->json($details);
    }
}
