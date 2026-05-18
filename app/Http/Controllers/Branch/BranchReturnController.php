<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\BranchReturn;
use App\Models\BranchReturnItem;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class BranchReturnController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->warehouse_id) {
            $count = $user->warehouses->count();
            if ($count === 1) {
                $user->update(['warehouse_id' => $user->warehouses->first()->id]);
            } elseif ($count > 1) {
                return redirect()->route('branch.dashboard')->with('error', 'Pilih cabang terlebih dahulu.');
            }
        }
        $warehouse = $user->warehouse;
        return view('branch.returns.index', compact('warehouse'))->with('sb', 'BranchReturn');
    }

    public function getall()
    {
        $user    = Auth::user();
        $returns = BranchReturn::with('requester')
            ->when($user->warehouse_id, fn($q) => $q->where('branch_warehouse_id', $user->warehouse_id))
            ->orderByDesc('created_at');

        return DataTables::of($returns)
            ->addIndexColumn()
            ->editColumn('status', fn($r) => $r->status_label)
            ->addColumn('total_items', fn($r) => $r->items()->count() . ' item')
            ->addColumn('action', function ($r) {
                $user = Auth::user();
                $canReview = in_array($user->role, ['super_admin', 'admin', 'store_manager']);
                $btn = '<a href="' . route('branch.returns.show', $r->id) . '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>';
                if ($canReview) {
                    $btn .= ' <a href="' . route('admin.branch.returns.show', $r->id) . '" class="btn btn-sm btn-warning" title="Review"><i class="fas fa-check"></i> Review</a>';
                }
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $warehouse = Auth::user()->warehouse;
        $batches   = $warehouse
            ? ProductBatch::with(['product.merek', 'variant.netto'])
                ->where('warehouse_id', $warehouse->id)
                ->where('qty', '>', 0)
                ->get()
            : collect();

        return view('branch.returns.create', compact('warehouse', 'batches'))->with('sb', 'BranchReturn');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_code'            => 'required|string|max:50',
            'reason'                    => 'nullable|string',
            'items'                     => 'required|array|min:1',
            'items.*.product_batch_id'  => 'required|exists:product_batches,id',
            'items.*.qty'               => 'required|integer|min:1',
            'items.*.reason'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $user      = Auth::user();
        $warehouse = $user->warehouse;
        if (!$warehouse) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum memiliki cabang aktif.'], 400);
        }

        try {
            DB::beginTransaction();

            $branchReturn = BranchReturn::create([
                'reference_number'   => BranchReturn::generateReferenceNumber($request->warehouse_code ?? ''),
                'branch_warehouse_id' => $warehouse->id,
                'warehouse_code'     => $request->warehouse_code,
                'requested_by'       => $user->id,
                'status'             => 'pending',
                'reason'             => $request->reason,
            ]);

            foreach ($request->items as $item) {
                $batch = ProductBatch::findOrFail($item['product_batch_id']);

                if ($batch->qty < $item['qty']) {
                    throw new \Exception("Stok batch {$batch->batch_no} tidak cukup untuk di-return. Tersedia: {$batch->qty}");
                }

                BranchReturnItem::create([
                    'branch_return_id'   => $branchReturn->id,
                    'product_id'         => $batch->product_id,
                    'product_variant_id' => $batch->product_variant_id,
                    'product_batch_id'   => $batch->id,
                    'qty'                => $item['qty'],
                    'reason'             => $item['reason'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'status'   => 'success',
                'message'  => 'Pengajuan return barang berhasil dibuat',
                'redirect' => route('branch.returns.show', $branchReturn->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $warehouse = Auth::user()->warehouse;
        $return    = BranchReturn::with([
            'items.product.merek',
            'items.variant',
            'items.batch',
            'requester',
            'approver',
            'receiver',
            'warehouse',
        ])->when($warehouse, fn($q) => $q->where('branch_warehouse_id', $warehouse->id))
            ->findOrFail($id);

        return view('branch.returns.show', compact('return', 'warehouse'))->with('sb', 'BranchReturn');
    }

    /**
     * Cabang konfirmasi pengiriman barang return ke pusat
     */
    public function confirmShip(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'receipt_photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $user         = Auth::user();
        $warehouse    = $user->warehouse;
        if (!$warehouse) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum memiliki cabang aktif.'], 400);
        }
        $branchReturn = BranchReturn::where('branch_warehouse_id', $warehouse->id)
            ->where('status', 'approved')
            ->findOrFail($id);

        try {
            DB::beginTransaction();

            $photoPath = null;
            if ($request->hasFile('receipt_photo')) {
                $photoPath = $request->file('receipt_photo')->store('branch/return_receipts', 'public');
            }

            // Kurangi stok dari warehouse cabang
            foreach ($branchReturn->items as $item) {
                $batch = ProductBatch::findOrFail($item->product_batch_id);
                if ($batch->qty < $item->qty) {
                    throw new \Exception("Stok batch {$batch->batch_no} tidak cukup. Tersedia: {$batch->qty}");
                }
                $batch->decrement('qty', $item->qty);
            }

            $branchReturn->update([
                'status'        => 'shipped',
                'shipped_at'    => now(),
                'receipt_photo' => $photoPath,
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Konfirmasi pengiriman berhasil. Stok cabang telah dikurangi.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Batalkan return (hanya pending)
     */
    public function cancel($id)
    {
        $user         = Auth::user();
        $warehouse    = $user->warehouse;
        if (!$warehouse) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum memiliki cabang aktif.'], 400);
        }
        $branchReturn = BranchReturn::where('branch_warehouse_id', $warehouse->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        $branchReturn->update(['status' => 'cancelled']);
        return response()->json(['status' => 'success', 'message' => 'Return berhasil dibatalkan.']);
    }
}
