<?php

namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\SupplierDeliveryNote;
use App\Models\SupplierDeliveryNoteItem;
use App\Models\Supplier;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class SupplierDeliveryNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $restrictedMethods = ['create', 'store', 'edit', 'update', 'destroy'];

            if ($user && in_array($request->route()->getActionMethod(), $restrictedMethods)) {
                if (!$user->canEdit('access_supplier_delivery_notes')) {
                    if ($request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses untuk tindakan ini.'], 403);
                    }
                    return redirect()->route('admin.purchasing.supplier_delivery_notes.index')->with('error', 'Anda tidak memiliki akses untuk tindakan ini.');
                }
            }

            return $next($request);
        });
    }

    public function index()
    {
        return view('admin.purchasing.supplier_delivery_notes.index')->with('sb', 'SupplierDeliveryNote');
    }

    public function getall()
    {
        $sdns = SupplierDeliveryNote::with(['supplier', 'user', 'items.batch.product.merek', 'items.batch.variant.netto'])
            ->orderBy('transaction_date', 'desc');

        return DataTables::of($sdns)
            ->addIndexColumn()
            ->addColumn('dokumen', function ($sdn) {
                $sjNumber = '<span class="po-number">' . e($sdn->sj_number) . '</span>';
                $date = '<small class="text-muted d-block">' . $sdn->transaction_date->format('d/m/Y') . '</small>';
                return $sjNumber . $date;
            })
            ->addColumn('supplier_name', function ($sdn) {
                return $sdn->supplier ? $sdn->supplier->name : '-';
            })
            ->addColumn('surat_jalan', function ($sdn) {
                return '<span>' . e($sdn->delivery_note_number ?? '-') . '</span>';
            })
            ->addColumn('produk', function ($sdn) {
                if ($sdn->items->isEmpty()) {
                    return '-';
                }
                return $sdn->items->map(function ($item) {
                    $parts = [];
                    if ($item->batch && $item->batch->product) {
                        $product = $item->batch->product;
                        if ($product->merek) {
                            $parts[] = e($product->merek->name);
                        }
                        $parts[] = e($product->name);
                        if ($item->batch->variant && $item->batch->variant->netto) {
                            $n = $item->batch->variant->netto;
                            $nettoText = trim($n->netto_value . ' ' . $n->satuan);
                            if ($nettoText) {
                                $parts[] = e($nettoText);
                            }
                        }
                    }
                    if ($item->batch) {
                        $parts[] = '<small class="text-muted">[' . e($item->batch->batch_no) . ']</small>';
                    }
                    $parts[] = '<span class="font-weight-bold">' . $item->qty . '</span>';
                    return '<div>' . implode(' ', $parts) . '</div>';
                })->implode('');
            })
            ->addColumn('user_name', function ($sdn) {
                return $sdn->user ? $sdn->user->name : '-';
            })
            ->addColumn('action', function ($sdn) {
                $isFinance = auth()->user()->isFinance();
                $editBtn = !$isFinance
                    ? '<a class="dropdown-item has-icon" href="' . route('admin.purchasing.supplier_delivery_notes.edit', $sdn->id) . '"><i class="fas fa-edit text-warning"></i> Edit</a>'
                    : '';
                $deleteBtn = !$isFinance
                    ? '<div class="dropdown-divider"></div><a class="dropdown-item has-icon text-danger btn-delete" href="javascript:void(0)" data-id="' . $sdn->id . '"><i class="fas fa-trash"></i> Hapus</a>'
                    : '';
                return '
                    <div class="dropdown d-inline dropleft">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">Aksi</button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item has-icon" href="' . route('admin.purchasing.supplier_delivery_notes.show', $sdn->id) . '"><i class="fas fa-eye text-info"></i> Detail</a>
                            ' . $editBtn . '
                            ' . $deleteBtn . '
                        </div>
                    </div>';
            })
            ->rawColumns(['dokumen', 'surat_jalan', 'produk', 'action'])
            ->make(true);
    }

    public function create()
    {
        $sj_number = SupplierDeliveryNote::generateSJNumber();
        $suppliers = Supplier::where('status', 'active')->get();
        return view('admin.purchasing.supplier_delivery_notes.create', compact('sj_number', 'suppliers'))->with('sb', 'SupplierDeliveryNote');
    }

    public function getBatches(Request $request)
    {
        $search = $request->search ?? '';
        $supplierId = $request->supplier_id;

        $query = ProductBatch::with(['product.merek', 'variant.netto', 'warehouse'])
            ->where('qty', '>', 0);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($search) {
            $words = array_filter(explode(' ', $search));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($subQ) use ($word) {
                        $subQ->where('batch_no', 'like', "%{$word}%")
                            ->orWhereHas('product', function ($pq) use ($word) {
                                $pq->where('name', 'like', "%{$word}%")
                                    ->orWhereHas('merek', function ($mq) use ($word) {
                                        $mq->where('name', 'like', "%{$word}%");
                                    });
                            });
                    });
                }
            });
        }

        $batches = $query->limit(30)->get();

        $results = [];
        foreach ($batches as $batch) {
            $parts = [];
            if ($batch->product) {
                if ($batch->product->merek) {
                    $parts[] = $batch->product->merek->name;
                }
                $parts[] = $batch->product->name;
                if ($batch->variant && $batch->variant->netto) {
                    $n = $batch->variant->netto;
                    $nettoText = trim($n->netto_value . ' ' . $n->satuan);
                    if ($nettoText) {
                        $parts[] = $nettoText;
                    }
                }
            }

            $results[] = [
                'id' => $batch->id,
                'text' => implode(' ', $parts) . ' [' . $batch->batch_no . ']',
                'batch_no' => $batch->batch_no,
                'product_name' => implode(' ', array_filter($parts)),
                'product_id' => $batch->product_id,
                'variant_id' => $batch->product_variant_id,
                'warehouse_id' => $batch->warehouse_id,
                'warehouse_name' => $batch->warehouse ? $batch->warehouse->name : '-',
                'current_stock' => $batch->current_stock,
                'initial_qty' => $batch->qty,
                'buy_price' => $batch->buy_price,
                'expiry_date' => $batch->expiry_date ? $batch->expiry_date->format('d/m/Y') : '-',
            ];
        }

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'delivery_note_number' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $sdn = SupplierDeliveryNote::create([
                'sj_number' => SupplierDeliveryNote::generateSJNumber(),
                'supplier_id' => $request->supplier_id,
                'user_id' => Auth::id(),
                'delivery_note_number' => $request->delivery_note_number,
                'transaction_date' => $request->transaction_date,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                SupplierDeliveryNoteItem::create([
                    'supplier_delivery_note_id' => $sdn->id,
                    'product_batch_id' => $item['product_batch_id'],
                    'qty' => $item['qty'],
                    'notes' => $item['item_notes'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Surat Jalan Supplier berhasil disimpan',
                'redirect' => route('admin.purchasing.supplier_delivery_notes.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $sdn = SupplierDeliveryNote::with([
            'supplier',
            'user',
            'items.batch.product.merek',
            'items.batch.variant.netto',
            'items.batch.warehouse'
        ])->findOrFail($id);

        return view('admin.purchasing.supplier_delivery_notes.show', compact('sdn'))->with('sb', 'SupplierDeliveryNote');
    }

    public function edit($id)
    {
        $sdn = SupplierDeliveryNote::with([
            'supplier',
            'user',
            'items.batch.product.merek',
            'items.batch.variant.netto',
            'items.batch.warehouse'
        ])->findOrFail($id);
        $suppliers = Supplier::where('status', 'active')->get();

        return view('admin.purchasing.supplier_delivery_notes.edit', compact('sdn', 'suppliers'))->with('sb', 'SupplierDeliveryNote');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'delivery_note_number' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $sdn = SupplierDeliveryNote::findOrFail($id);
            $sdn->update([
                'supplier_id' => $request->supplier_id,
                'delivery_note_number' => $request->delivery_note_number,
                'transaction_date' => $request->transaction_date,
                'notes' => $request->notes,
            ]);

            $sdn->items()->delete();

            foreach ($request->items as $item) {
                SupplierDeliveryNoteItem::create([
                    'supplier_delivery_note_id' => $sdn->id,
                    'product_batch_id' => $item['product_batch_id'],
                    'qty' => $item['qty'],
                    'notes' => $item['item_notes'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Surat Jalan Supplier berhasil diperbarui',
                'redirect' => route('admin.purchasing.supplier_delivery_notes.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $sdn = SupplierDeliveryNote::findOrFail($id);
            $sdn->items()->delete();
            $sdn->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Surat Jalan Supplier berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }
}
