<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\BranchStockRequest;
use App\Models\BranchStockRequestItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StockRequestController extends Controller
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
        return view('branch.stock_requests.index', compact('warehouse'))->with('sb', 'BranchStockRequest');
    }

    public function getall()
    {
        $user     = Auth::user();
        $requests = BranchStockRequest::with('requester')
            ->when($user->warehouse_id, fn($q) => $q->where('branch_warehouse_id', $user->warehouse_id))
            ->orderByDesc('created_at');

        return DataTables::of($requests)
            ->addIndexColumn()
            ->editColumn('status', fn($r) => $r->status_label)
            ->addColumn('total_items', fn($r) => $r->items()->count() . ' item')
            ->addColumn('action', function ($r) {
                $user = Auth::user();
                $canReview = in_array($user->role, ['super_admin', 'admin', 'store_manager']);
                
                $btn = '<div class="dropdown d-inline">
                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Aksi
                            </button>
                            <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 29px, 0px); top: 0px; left: 0px; will-change: transform;">
                                <a class="dropdown-item has-icon" href="' . route('branch.stock_requests.show', $r->id) . '"><i class="fas fa-eye"></i> Detail</a>';
                
                if ($r->status === 'pending') {
                    $btn .= '<a class="dropdown-item has-icon" href="' . route('branch.stock_requests.edit', $r->id) . '"><i class="fas fa-edit"></i> Edit</a>';
                }
                
                if ($canReview) {
                    $btn .= '<a class="dropdown-item has-icon" href="' . route('admin.branch.stock_requests.show', $r->id) . '"><i class="fas fa-check"></i> Review</a>';
                }
                
                $btn .= '</div></div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $warehouse = Auth::user()->warehouse;
        $variants  = \App\Models\ProductVariant::with(['netto.product.merek'])
            ->whereHas('netto.product')
            ->get()
            ->map(fn($v) => [
                'id'           => $v->id,
                'product_id'   => $v->netto->product_id,
                'label'        => trim(implode(' ', array_filter([
                    $v->netto->product->merek->name ?? '',
                    $v->netto->product->name ?? '',
                    trim(($v->netto->netto_value ?? '') . ' ' . ($v->netto->satuan ?? '')),
                ]))),
            ]);
        return view('branch.stock_requests.create', compact('warehouse', 'variants'))->with('sb', 'BranchStockRequest');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notes'                      => 'nullable|string',
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty_requested'      => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $user      = Auth::user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return response()->json(['status' => 'error', 'message' => 'Akun Anda belum terhubung ke gudang manapun.'], 403);
        }

        try {
            DB::beginTransaction();

            $whCode = $warehouse->code ?? '';
            $stockRequest = BranchStockRequest::create([
                'reference_number'   => BranchStockRequest::generateReferenceNumber($whCode),
                'branch_warehouse_id' => $warehouse->id,
                'requested_by'       => $user->id,
                'status'             => 'pending',
                'notes'              => $request->notes,
            ]);

            foreach ($request->items as $item) {
                BranchStockRequestItem::create([
                    'branch_stock_request_id' => $stockRequest->id,
                    'product_id'              => $item['product_id'],
                    'product_variant_id'      => $item['product_variant_id'] ?: null,
                    'qty_requested'           => $item['qty_requested'],
                    'notes'                   => $item['notes'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'status'   => 'success',
                'message'  => 'Pengajuan barang berhasil dibuat',
                'redirect' => route('branch.stock_requests.show', $stockRequest->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $warehouse = Auth::user()->warehouse;
        $request   = BranchStockRequest::with('items')->where('branch_warehouse_id', $warehouse->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        $variants  = \App\Models\ProductVariant::with(['netto.product.merek'])
            ->whereHas('netto.product')
            ->get()
            ->map(fn($v) => [
                'id'           => $v->id,
                'product_id'   => $v->netto->product_id,
                'label'        => trim(implode(' ', array_filter([
                    $v->netto->product->merek->name ?? '',
                    $v->netto->product->name ?? '',
                    trim(($v->netto->netto_value ?? '') . ' ' . ($v->netto->satuan ?? '')),
                ]))),
            ]);

        return view('branch.stock_requests.edit', compact('request', 'warehouse', 'variants'))->with('sb', 'BranchStockRequest');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'notes'                      => 'nullable|string',
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty_requested'      => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $user      = Auth::user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return response()->json(['status' => 'error', 'message' => 'Akun Anda belum terhubung ke gudang manapun.'], 403);
        }

        $stockRequest = BranchStockRequest::where('branch_warehouse_id', $warehouse->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        try {
            DB::beginTransaction();

            $stockRequest->update([
                'notes' => $request->notes,
            ]);

            // Clear old items and recreate
            BranchStockRequestItem::where('branch_stock_request_id', $stockRequest->id)->delete();

            foreach ($request->items as $item) {
                BranchStockRequestItem::create([
                    'branch_stock_request_id' => $stockRequest->id,
                    'product_id'              => $item['product_id'],
                    'product_variant_id'      => $item['product_variant_id'] ?: null,
                    'qty_requested'           => $item['qty_requested'],
                    'notes'                   => $item['notes'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'status'   => 'success',
                'message'  => 'Pengajuan barang berhasil diperbarui',
                'redirect' => route('branch.stock_requests.show', $stockRequest->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $warehouse = Auth::user()->warehouse;
        $request   = BranchStockRequest::with([
            'items.product.merek',
            'items.variant.netto',
            'requester',
            'approver',
            'shipper',
            'receiver',
            'warehouse',
        ])->when($warehouse, fn($q) => $q->where('branch_warehouse_id', $warehouse->id))
            ->findOrFail($id);

        return view('branch.stock_requests.show', compact('request', 'warehouse'))->with('sb', 'BranchStockRequest');
    }

    /**
     * Cabang konfirmasi penerimaan barang
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

        $user         = Auth::user();
        $warehouse    = $user->warehouse;
        if (!$warehouse) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum memiliki cabang aktif.'], 400);
        }
        $stockRequest = BranchStockRequest::where('branch_warehouse_id', $warehouse->id)
            ->where('status', 'shipped')
            ->findOrFail($id);

        try {
            DB::beginTransaction();

            // Handle foto bukti terima
            $photoPath = null;
            if ($request->hasFile('receipt_photo')) {
                $photoPath = $request->file('receipt_photo')->store('branch/receipts', 'public');
            }

            // Tambah stok ke product_batches warehouse cabang
            $mainWarehouse = \App\Models\Warehouse::where('type', 'main')->first();
            foreach ($stockRequest->items as $item) {
                if (!$item->qty_approved || $item->qty_approved < 1) continue;

                // Cari/clone batch dari pusat
                $sourceBatch = \App\Models\ProductBatch::where('warehouse_id', $mainWarehouse?->id)
                    ->where('product_id', $item->product_id)
                    ->when($item->product_variant_id, fn($q) => $q->where('product_variant_id', $item->product_variant_id))
                    ->latest()
                    ->first();

                \App\Models\ProductBatch::create([
                    'product_id'         => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'warehouse_id'       => $warehouse->id,
                    'batch_no'           => $sourceBatch ? $sourceBatch->batch_no : ('BSR-' . $stockRequest->reference_number),
                    'qty'                => $item->qty_approved,
                    'buy_price'          => $sourceBatch?->buy_price,
                    'expiry_date'        => $sourceBatch?->expiry_date,
                ]);
            }

            $stockRequest->update([
                'status'       => 'received',
                'received_by'  => $user->id,
                'receipt_notes' => $request->receipt_notes,
                'receipt_photo' => $photoPath,
                'received_at'  => now(),
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Barang berhasil dikonfirmasi diterima. Stok cabang telah diperbarui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cabang batalkan pengajuan (hanya status pending)
     */
    public function cancel($id)
    {
        $user         = Auth::user();
        $warehouse    = $user->warehouse;
        if (!$warehouse) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum memiliki cabang aktif.'], 400);
        }
        $stockRequest = BranchStockRequest::where('branch_warehouse_id', $warehouse->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        $stockRequest->update(['status' => 'cancelled']);
        return response()->json(['status' => 'success', 'message' => 'Pengajuan berhasil dibatalkan.']);
    }
}
