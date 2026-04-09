<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Warehouse;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class StockMovementController extends Controller
{
    public function index()
    {
        return view('admin.inventory.stock_movements.index')->with('sb', 'StockMovement');
    }

    public function getall()
    {
        $movements = StockMovement::with(['fromWarehouse', 'toWarehouse', 'user'])
            ->orderBy('created_at', 'desc');

        return DataTables::of($movements)
            ->addIndexColumn()
            ->addColumn('from', fn($m) => $m->fromWarehouse->name ?? '-')
            ->addColumn('to', fn($m) => $m->toWarehouse->name ?? '-')
            ->editColumn('status', function ($m) {
                $badges = [
                    'pending'   => 'badge-warning',
                    'transit'   => 'badge-info',
                    'completed' => 'badge-success',
                    'cancelled' => 'badge-danger',
                ];
                return '<span class="badge ' . ($badges[$m->status] ?? 'badge-secondary') . '">' . strtoupper($m->status) . '</span>';
            })
            ->addColumn('action', function ($m) {
                $btn = '<div class="btn-group">';
                $btn .= '<a href="' . route('admin.stock_movements.show', $m->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                if ($m->status === 'pending') {
                    $btn .= '<button class="btn btn-sm btn-primary btn-ship ml-1" data-id="' . $m->id . '"><i class="fas fa-truck"></i> Kirim</button>';
                }
                if ($m->status === 'transit') {
                    $btn .= '<button class="btn btn-sm btn-success btn-receive ml-1" data-id="' . $m->id . '"><i class="fas fa-check"></i> Terima</button>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $warehouses = Warehouse::where('status', 'active')->get();
        return view('admin.inventory.stock_movements.create', compact('warehouses'))->with('sb', 'StockMovement');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_warehouse_id'        => 'required|exists:warehouses,id',
            'to_warehouse_id'          => 'required|exists:warehouses,id|different:from_warehouse_id',
            'items'                    => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.qty'              => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $movement = StockMovement::create([
                'reference_number'  => 'MV-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)),
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id'   => $request->to_warehouse_id,
                'user_id'           => Auth::id(),
                'status'            => 'pending',
                'notes'             => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $batch = ProductBatch::findOrFail($item['product_batch_id']);
                StockMovementItem::create([
                    'stock_movement_id'  => $movement->id,
                    'product_id'         => $batch->product_id,
                    'product_variant_id' => $batch->product_variant_id ?: null,
                    'product_batch_id'   => $batch->id,
                    'qty'                => $item['qty'],
                ]);
            }

            DB::commit();
            return response()->json([
                'status'   => 'success',
                'message'  => 'Stock Movement berhasil dibuat',
                'redirect' => route('admin.stock_movements.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $movement = StockMovement::with([
            'items.product',
            'items.variant',
            'items.batch',
            'fromWarehouse',
            'toWarehouse',
            'user',
            'receiver',
        ])->findOrFail($id);

        return view('admin.inventory.stock_movements.show', compact('movement'))->with('sb', 'StockMovement');
    }

    public function ship($id)
    {
        try {
            DB::beginTransaction();
            $movement = StockMovement::with('items.batch')->findOrFail($id);

            if ($movement->status !== 'pending') {
                throw new \Exception('Movement sudah diproses atau dibatalkan.');
            }

            foreach ($movement->items as $item) {
                $batch = ProductBatch::find($item->product_batch_id);
                if (!$batch || $batch->qty < $item->qty) {
                    $available = $batch ? $batch->qty : 0;
                    throw new \Exception("Stok tidak cukup untuk batch {$batch->batch_no}. Tersedia: {$available}");
                }
                $batch->decrement('qty', $item->qty);
            }

            $movement->update([
                'status'     => 'transit',
                'shipped_at' => now(),
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Barang dikirim — stok gudang asal dikurangi']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function receive($id)
    {
        try {
            DB::beginTransaction();
            $movement = StockMovement::with('items.batch')->findOrFail($id);

            if ($movement->status !== 'transit') {
                throw new \Exception('Movement harus berstatus transit sebelum bisa diterima.');
            }

            foreach ($movement->items as $item) {
                $sourceBatch = $item->batch;
                // Add stock to destination warehouse, same batch_no for full traceability
                ProductBatch::create([
                    'product_id'         => $item->product_id,
                    'product_variant_id' => $item->product_variant_id ?: null,
                    'warehouse_id'       => $movement->to_warehouse_id,
                    'batch_no'           => $sourceBatch->batch_no,
                    'qty'                => $item->qty,
                    'buy_price'          => $sourceBatch->buy_price,
                    'expiry_date'        => $sourceBatch->expiry_date ?? null,
                ]);
            }

            $movement->update([
                'status'      => 'completed',
                'received_at' => now(),
                'received_by' => Auth::id(),
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Barang diterima — stok gudang tujuan bertambah']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
