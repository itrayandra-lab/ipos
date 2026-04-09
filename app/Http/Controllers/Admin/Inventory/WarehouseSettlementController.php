<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\WarehouseSettlement;
use App\Models\WarehouseSettlementItem;
use App\Models\Warehouse;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class WarehouseSettlementController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::where('status', 'active')->get();
        return view('admin.inventory.settlements.index', compact('warehouses'))->with('sb', 'Settlement');
    }

    public function getall(Request $request)
    {
        $query = WarehouseSettlement::with(['warehouse', 'creator'])
            ->orderBy('created_at', 'desc');

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('warehouse_name', fn($s) => $s->warehouse->name ?? '-')
            ->addColumn('period', fn($s) => $s->period_start->format('d/m/Y') . ' – ' . $s->period_end->format('d/m/Y'))
            ->editColumn('total_amount', fn($s) => 'Rp ' . number_format($s->total_amount, 0, ',', '.'))
            ->editColumn('status', function ($s) {
                $badges = [
                    'draft'    => 'badge-secondary',
                    'pending'  => 'badge-warning',
                    'verified' => 'badge-info',
                    'paid'     => 'badge-success',
                    'rejected' => 'badge-danger',
                ];
                return '<span class="badge ' . ($badges[$s->status] ?? 'badge-secondary') . '">' . strtoupper($s->status) . '</span>';
            })
            ->addColumn('action', function ($s) {
                $btn = '<div class="btn-group">';
                $btn .= '<a href="' . route('admin.settlements.show', $s->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                if (in_array($s->status, ['draft', 'rejected'])) {
                    $btn .= '<button class="btn btn-sm btn-warning btn-submit ml-1" data-id="' . $s->id . '"><i class="fas fa-paper-plane"></i> Submit</button>';
                }
                if ($s->status === 'pending') {
                    $btn .= '<button class="btn btn-sm btn-success btn-verify ml-1" data-id="' . $s->id . '"><i class="fas fa-check-double"></i> Verif</button>';
                    $btn .= '<button class="btn btn-sm btn-danger btn-reject ml-1" data-id="' . $s->id . '"><i class="fas fa-times"></i> Tolak</button>';
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
        return view('admin.inventory.settlements.create', compact('warehouses'))->with('sb', 'Settlement');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $total = 0;
            $itemsToCreate = [];

            if ($request->has('items') && is_array($request->items)) {
                // Manual Input logic
                foreach ($request->items as $item) {
                    $qty = (int)($item['qty'] ?? 0);
                    $price = (float)($item['price'] ?? 0);
                    $subtotal = $qty * $price;
                    $total += $subtotal;

                    $itemsToCreate[] = [
                        'product_id'         => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'] ?? null,
                        'quantity_sold'      => $qty,
                        'unit_price'         => $price,
                        'subtotal'           => $subtotal,
                    ];
                }
            } else {
                // Auto-generate items from transactions in that period for this warehouse
                $transactions = Transaction::where('warehouse_id', $request->warehouse_id)
                    ->whereBetween('created_at', [$request->period_start . ' 00:00:00', $request->period_end . ' 23:59:59'])
                    ->with('items.product')
                    ->get();

                foreach ($transactions as $tx) {
                    foreach ($tx->items as $item) {
                        $subtotal = ($item->price ?? 0) * ($item->qty ?? 0) - ($item->discount ?? 0);
                        $total += $subtotal;

                        $existingKey = $item->product_id . '-' . ($item->product_variant_id ?? 0);
                        if (isset($itemsToCreate[$existingKey])) {
                            $itemsToCreate[$existingKey]['quantity_sold'] += $item->qty;
                            $itemsToCreate[$existingKey]['subtotal'] += $subtotal;
                        } else {
                            $itemsToCreate[$existingKey] = [
                                'product_id'         => $item->product_id,
                                'product_variant_id' => $item->product_variant_id,
                                'quantity_sold'      => $item->qty,
                                'unit_price'         => $item->price ?? 0,
                                'subtotal'           => $subtotal,
                            ];
                        }
                    }
                }
            }

            $settlement = WarehouseSettlement::create([
                'settlement_no' => 'STL-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)),
                'warehouse_id'  => $request->warehouse_id,
                'period_start'  => $request->period_start,
                'period_end'    => $request->period_end,
                'total_amount'  => $total,
                'status'        => 'draft',
                'notes'         => $request->notes,
                'created_by'    => Auth::id(),
            ]);

            foreach ($itemsToCreate as $item) {
                WarehouseSettlementItem::create(array_merge($item, [
                    'warehouse_settlement_id' => $settlement->id,
                ]));
            }

            DB::commit();
            return response()->json([
                'status'   => 'success',
                'message'  => 'Settlement berhasil dibuat',
                'redirect' => route('admin.settlements.show', $settlement->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $settlement = WarehouseSettlement::with(['items.product', 'items.variant', 'warehouse', 'creator', 'verifier'])
            ->findOrFail($id);
        return view('admin.inventory.settlements.show', compact('settlement'))->with('sb', 'Settlement');
    }

    public function submit($id)
    {
        $settlement = WarehouseSettlement::findOrFail($id);
        if (!in_array($settlement->status, ['draft', 'rejected'])) {
            return response()->json(['status' => 'error', 'message' => 'Status tidak valid untuk disubmit.']);
        }
        $settlement->update(['status' => 'pending']);
        return response()->json(['status' => 'success', 'message' => 'Settlement disubmit untuk verifikasi.']);
    }

    public function verify($id)
    {
        $settlement = WarehouseSettlement::findOrFail($id);
        if ($settlement->status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Settlement tidak dalam status pending.']);
        }
        $settlement->update([
            'status'      => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);
        return response()->json(['status' => 'success', 'message' => 'Settlement berhasil diverifikasi.']);
    }

    public function reject(Request $request, $id)
    {
        $settlement = WarehouseSettlement::findOrFail($id);
        if ($settlement->status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Settlement tidak dalam status pending.']);
        }
        $settlement->update([
            'status' => 'rejected',
            'notes'  => ($settlement->notes ? $settlement->notes . "\n" : '') . 'Ditolak: ' . ($request->reason ?? '-'),
        ]);
        return response()->json(['status' => 'success', 'message' => 'Settlement ditolak.']);
    }
}
