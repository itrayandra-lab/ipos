<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Http\Controllers\Controller;
use App\Models\BranchReturn;
use App\Models\BranchReturnItem;
use App\Models\ProductBatch;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class BranchReturnApprovalController extends Controller
{
    public function index()
    {
        return view('admin.branch.returns.index')->with('sb', 'BranchReturnApproval');
    }

    public function getall(Request $request)
    {
        $query = BranchReturn::with(['warehouse', 'requester'])
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
            ->addColumn('action', fn($r) => '
                <a href="' . route('admin.branch.returns.show', $r->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
            ')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $return = BranchReturn::with([
            'items.product.merek',
            'items.variant',
            'items.batch',
            'requester',
            'approver',
            'receiver',
            'warehouse',
        ])->findOrFail($id);

        return view('admin.branch.returns.show', compact('return'))->with('sb', 'BranchReturnApproval');
    }

    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $branchReturn = BranchReturn::where('status', 'pending')->findOrFail($id);
        $branchReturn->update([
            'status'         => 'approved',
            'approved_by'    => Auth::id(),
            'approval_notes' => $request->approval_notes,
            'approved_at'    => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Return disetujui. Cabang dapat mengirim barang.']);
    }

    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $branchReturn = BranchReturn::where('status', 'pending')->findOrFail($id);
        $branchReturn->update([
            'status'           => 'rejected',
            'approved_by'      => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
            'approved_at'      => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Return berhasil ditolak.']);
    }

    /**
     * Pusat konfirmasi terima barang return dari cabang — tambah stok pusat kembali
     */
    public function confirmReceive(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'receipt_notes' => 'nullable|string',
            'receipt_photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $branchReturn  = BranchReturn::with('items.batch')->where('status', 'shipped')->findOrFail($id);
        $mainWarehouse = Warehouse::where('type', 'main')->first();

        try {
            DB::beginTransaction();

            $photoPath = null;
            if ($request->hasFile('receipt_photo')) {
                $photoPath = $request->file('receipt_photo')->store('branch/return_receipts', 'public');
            }

            // Tambah kembali stok ke gudang pusat
            foreach ($branchReturn->items as $item) {
                $sourceBatch = $item->batch;

                ProductBatch::create([
                    'product_id'         => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'warehouse_id'       => $mainWarehouse->id,
                    'batch_no'           => $sourceBatch ? $sourceBatch->batch_no : ('BRT-' . $branchReturn->reference_number),
                    'qty'                => $item->qty,
                    'buy_price'          => $sourceBatch?->buy_price,
                    'expiry_date'        => $sourceBatch?->expiry_date,
                ]);
            }

            $branchReturn->update([
                'status'        => 'received',
                'received_by'   => Auth::id(),
                'receipt_notes' => $request->receipt_notes,
                'receipt_photo' => $request->hasFile('receipt_photo') ? $photoPath : $branchReturn->receipt_photo,
                'received_at'   => now(),
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Return diterima. Stok gudang pusat telah dikembalikan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
