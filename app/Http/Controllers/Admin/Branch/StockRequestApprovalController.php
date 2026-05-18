<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Http\Controllers\Controller;
use App\Models\BranchStockRequest;
use App\Models\BranchStockRequestItem;
use App\Models\ProductBatch;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StockRequestApprovalController extends Controller
{
    public function index()
    {
        return view('admin.branch.stock_requests.index')->with('sb', 'BranchApproval');
    }

    public function getall(Request $request)
    {
        $query = BranchStockRequest::with(['warehouse', 'requester'])
            ->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('warehouse_name', fn($r) => $r->warehouse->name ?? '-')
            ->addColumn('requester_name', fn($r) => $r->requester->name ?? '-')
            ->editColumn('status', fn($r) => $r->status_label)
            ->addColumn('total_items', fn($r) => $r->items()->count() . ' item')
            ->addColumn('action', function ($r) {
                $btn = '<a href="' . route('admin.branch.stock_requests.show', $r->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $request = BranchStockRequest::with([
            'items.product.merek',
            'items.variant.netto',
            'requester',
            'approver',
            'shipper',
            'receiver',
            'warehouse',
        ])->findOrFail($id);

        $mainWarehouse = Warehouse::where('type', 'main')->first();

        // Stok tersedia di gudang pusat per item
        $stockAvailability = [];
        foreach ($request->items as $item) {
            $available = ProductBatch::where('warehouse_id', $mainWarehouse?->id)
                ->where('product_id', $item->product_id)
                ->when($item->product_variant_id, fn($q) => $q->where('product_variant_id', $item->product_variant_id))
                ->sum('qty');
            $stockAvailability[$item->id] = $available;
        }

        return view('admin.branch.stock_requests.show', compact('request', 'mainWarehouse', 'stockAvailability'))->with('sb', 'BranchApproval');
    }

    /**
     * Approve pengajuan + set qty_approved per item
     */
    public function approve(Request $request, $id)
    {
        $stockRequest = BranchStockRequest::where('status', 'pending')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'approval_notes'       => 'nullable|string',
            'items'                => 'required|array',
            'items.*.id'           => 'required|exists:branch_stock_request_items,id',
            'items.*.qty_approved' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $caseStmts = [];
            $ids = [];
            foreach ($request->items as $itemData) {
                $id = (int) $itemData['id'];
                $qty = (int) $itemData['qty_approved'];
                $ids[] = $id;
                $caseStmts[] = "WHEN {$id} THEN {$qty}";
            }
            $idsStr = implode(',', $ids);
            $caseStr = implode(' ', $caseStmts);

            if ($ids) {
                DB::statement("UPDATE branch_stock_request_items
                    SET qty_approved = CASE id {$caseStr} END
                    WHERE id IN ({$idsStr}) AND branch_stock_request_id = ?", [$stockRequest->id]);
            }

            $stockRequest->update([
                'status'         => 'approved',
                'approved_by'    => Auth::id(),
                'approval_notes' => $request->approval_notes,
                'approved_at'    => now(),
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Pengajuan berhasil disetujui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject pengajuan
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $stockRequest = BranchStockRequest::where('status', 'pending')->findOrFail($id);
        $stockRequest->update([
            'status'           => 'rejected',
            'approved_by'      => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
            'approved_at'      => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Pengajuan berhasil ditolak.']);
    }

    /**
     * Pusat kirim barang — kurangi stok pusat, status jadi shipped
     */
    public function ship(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'shipping_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $stockRequest  = BranchStockRequest::with('items')->where('status', 'approved')->findOrFail($id);
        $mainWarehouse = Warehouse::where('type', 'main')->first();

        try {
            DB::beginTransaction();

            foreach ($stockRequest->items as $item) {
                if (!$item->qty_approved || $item->qty_approved < 1) continue;

                // Kurangi stok dari product_batches gudang pusat (FIFO)
                $remaining = $item->qty_approved;
                $batches   = ProductBatch::where('warehouse_id', $mainWarehouse?->id)
                    ->where('product_id', $item->product_id)
                    ->when($item->product_variant_id, fn($q) => $q->where('product_variant_id', $item->product_variant_id))
                    ->where('qty', '>', 0)
                    ->orderBy('created_at')
                    ->get();

                foreach ($batches as $batch) {
                    if ($remaining <= 0) break;
                    $deduct = min($batch->qty, $remaining);
                    $batch->decrement('qty', $deduct);
                    $remaining -= $deduct;
                }

                if ($remaining > 0) {
                    throw new \Exception("Stok gudang pusat tidak cukup untuk " . ($item->product->name ?? 'produk'));
                }
            }

            $stockRequest->update([
                'status'         => 'shipped',
                'shipped_by'     => Auth::id(),
                'shipping_notes' => $request->shipping_notes,
                'shipped_at'     => now(),
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Barang berhasil dikirim. Stok gudang pusat telah dikurangi.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
