<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\BranchSale;
use App\Models\BranchStockRequest;
use App\Models\BranchReturn;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user      = Auth::user();
        
        // If no active warehouse is set
        if (!$user->warehouse_id) {
            $count = $user->warehouses->count();
            if ($count === 1) {
                // If only one, set it automatically
                $user->update(['warehouse_id' => $user->warehouses->first()->id]);
            } elseif ($count > 1) {
                // If multiple, show selection page
                return view('branch.select_branch')->with('sb', 'BranchDashboard');
            }
        }

        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return view('branch.dashboard.index', [
                'warehouse'          => null,
                'totalStock'         => 0,
                'pendingRequests'    => 0,
                'monthlySales'       => 0,
                'pendingReturns'     => 0,
                'recentRequests'     => collect(),
                'recentSales'        => collect(),
            ])->with('sb', 'BranchDashboard');
        }

        $warehouseId = $warehouse->id;

        // Stok di warehouse cabang
        $totalStock = ProductBatch::where('warehouse_id', $warehouseId)->sum('qty');

        // Pengajuan pending
        $pendingRequests = BranchStockRequest::where('branch_warehouse_id', $warehouseId)
            ->where('status', 'pending')->count();

        // Penjualan bulan ini
        $monthlySales = BranchSale::where('branch_warehouse_id', $warehouseId)
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->sum('total_amount');

        // Return pending
        $pendingReturns = BranchReturn::where('branch_warehouse_id', $warehouseId)
            ->where('status', 'pending')->count();

        // 5 pengajuan terbaru
        $recentRequests = BranchStockRequest::where('branch_warehouse_id', $warehouseId)
            ->with('requester')
            ->latest()
            ->take(5)
            ->get();

        // 5 penjualan terbaru
        $recentSales = BranchSale::where('branch_warehouse_id', $warehouseId)
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('branch.dashboard.index', compact(
            'warehouse',
            'totalStock',
            'pendingRequests',
            'monthlySales',
            'pendingReturns',
            'recentRequests',
            'recentSales',
        ))->with('sb', 'BranchDashboard');
    }

    public function switchBranch($id)
    {
        $user = Auth::user();
        
        // Check if user has access to this warehouse
        $hasAccess = $user->warehouses()->where('warehouses.id', $id)->exists();
        
        if ($hasAccess || $user->isSuperAdmin()) {
            $user->update(['warehouse_id' => $id]);
            return redirect()->route('branch.dashboard')->with('message', 'Berhasil pindah cabang.');
        }

        return redirect()->back()->with('error', 'Anda tidak memiliki akses ke cabang ini.');
    }
}
