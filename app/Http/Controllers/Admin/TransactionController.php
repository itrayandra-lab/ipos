<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\ProductBatch;
use App\Models\TransactionItem;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\InvoiceService;

class TransactionController extends Controller
{
    public function generateInvoice(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->invoice_number) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah memiliki nomor invoice: ' . $transaction->invoice_number
            ], 400);
        }

        try {
            DB::transaction(function () use ($transaction) {
                $transaction->update([
                    'invoice_number' => InvoiceService::generate()
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil dibuat: ' . $transaction->invoice_number,
                'invoice_number' => $transaction->invoice_number
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        return view('admin.transaction.index')->with([
            'sb' => 'Transaction'
        ]);
    }

    public function getall(Request $request)
    {
        $query = Transaction::select(
            'transactions.id',
            'transactions.user_id',
            'transactions.total_amount',
            'transactions.payment_status',
            'transactions.delivery_type',
            'transactions.invoice_number',
            'transactions.created_at',
            'users.name as user_name'
        )
        ->join('users', 'transactions.user_id', '=', 'users.id')->orderBy('id', 'desc');

        // Apply filters
        if ($request->has('delivery_type') && !empty($request->delivery_type)) {
            $query->where('transactions.delivery_type', $request->delivery_type);
        }

        if ($request->has('payment_status') && !empty($request->payment_status)) {
            $query->where('transactions.payment_status', $request->payment_status);
        }

        if ($request->has('start_date') && !empty($request->start_date)) {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $query->where('transactions.created_at', '>=', $startDate);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            $query->where('transactions.created_at', '<=', $endDate);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('user.name', function ($transaction) {
                return $transaction->user_name;
            })
            ->addColumn('action', function ($transaction) {
                $invoiceBtn = '';
                if (!$transaction->invoice_number) {
                    $invoiceBtn = '<li><button type="button" onclick="generateInvoice(' . $transaction->id . ')" class="dropdown-item text-primary font-weight-bold"><i class="fas fa-file-invoice"></i> Buat Invoice Baru</button></li>';
                } else {
                    $invoiceBtn = '<li><a href="' . route('admin.sales.invoices.print', $transaction->id) . '" target="_blank" class="dropdown-item text-success"><i class="fas fa-print"></i> Cetak Invoice</a></li>';
                }

                return '<div class="dropdown d-inline dropleft">
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                        Action
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="' . url('admin/transactions/show/'. $transaction->id) . '" class="dropdown-item">Detail</a></li>
                        <li><a href="' . route('admin.transactions.edit', $transaction->id) . '" class="dropdown-item">Edit</a></li>
                        <li><a href="' . route('admin.transactions.print_struk', $transaction->id) . '" target="_blank" class="dropdown-item">Print Struk</a></li>
                        ' . $invoiceBtn . '
                        <div class="dropdown-divider"></div>
                        <li><button type="button" onclick="deleteTransaction(' . $transaction->id . ')" class="dropdown-item text-danger">Hapus</button></li>
                    </ul>
                </div>';
            })
            ->editColumn('created_at', function ($transaction) {
                return Carbon::parse($transaction->created_at)->format('d-m-Y H:i');
            })
            ->editColumn('total_amount', function ($transaction) {
                return 'Rp ' . number_format($transaction->total_amount, 0, ',', '.');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['user', 'items.product', 'items.batch'])->findOrFail($id);
        return view('admin.transaction.show', compact('transaction'))->with('sb', 'Transaction');
    }

    public function print(Request $request)
    {
        $query = Transaction::select(
            'transactions.id',
            'transactions.user_id',
            'transactions.total_amount',
            'transactions.payment_status',
            'transactions.delivery_type',
            'transactions.created_at',
            'users.name as user_name'
        )
        ->join('users', 'transactions.user_id', '=', 'users.id');

        // Apply filters
        if ($request->has('delivery_type') && !empty($request->delivery_type)) {
            $query->where('transactions.delivery_type', $request->delivery_type);
        }

        if ($request->has('payment_status') && !empty($request->payment_status)) {
            $query->where('transactions.payment_status', $request->payment_status);
        }

        if ($request->has('start_date') && !empty($request->start_date)) {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $query->where('transactions.created_at', '>=', $startDate);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            $query->where('transactions.created_at', '<=', $endDate);
        }

        $transactions = $query->orderBy('id', 'desc')->get();

        return view('admin.transaction.print', compact('transactions'));
    }

    public function printStruk($id)
    {
        $transaction = Transaction::with(['user', 'customer', 'items.product', 'items.batch', 'payments'])->findOrFail($id);
        $storeSetting = \App\Models\StoreSetting::find(1);
        return view('admin.transaction.print_struk', compact('transaction', 'storeSetting'));
    }

    public function edit($id)
    {
        $transaction = Transaction::with(['customer', 'items.product.merek', 'items.batch.variant'])
            ->findOrFail($id);
        
        $customers = Customer::orderBy('name')->get();
        
        $batches = ProductBatch::with(['product.merek', 'variant'])
            ->where('qty', '>', 0)
            ->whereHas('product', fn($q) => $q->where('status', 'Y'))
            ->get()
            ->sortBy(fn($batch) => ($batch->product->merek->name ?? '') . ' ' . ($batch->product->name ?? ''));

        $batchList = [];
        foreach ($batches as $batch) {
            $product     = $batch->product;
            $merekName   = ($product && $product->merek) ? trim($product->merek->name) : '';
            $productName = trim($product->name ?? '');
            $variantName = $batch->variant ? trim($batch->variant->variant_name) : '';
            
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
            $batchList[] = [
                'id'        => $batch->id,
                'text'      => $labelText . ' (' . $batch->batch_no . ' - ' . $batch->qty . ')',
                'price'     => $batch->variant->price ?? ($product->price_real > 0 ? $product->price_real : $product->price),
                'stock'     => $batch->qty,
                'buy_price' => $batch->buy_price ?? 0,
            ];
        }

        return view('admin.transaction.edit', compact('transaction', 'customers', 'batchList'))
            ->with('sb', 'Transaction');
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'transaction_date' => 'nullable|date',
            'payment_method' => 'required|string|max:50',
            'payment_status' => 'required|in:draft,unpaid,paid,canceled,credit',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $transaction, $id) {
                $totalAmount   = 0;
                $itemsToCreate = [];
                
                foreach ($request->items as $item) {
                    $batch    = ProductBatch::with('product')->findOrFail($item['product_batch_id']);
                    $product  = $batch->product;
                    $qty      = (int) $item['qty'];
                    $price    = (float) $item['price'];
                    $subtotal = $price * $qty;
                    $totalAmount += $subtotal;
                    $itemsToCreate[] = [
                        'product_id'       => $product->id,
                        'product_batch_id' => $batch->id,
                        'buy_price'        => $batch->buy_price ?? 0,
                        'qty'              => $qty,
                        'price'            => $price,
                        'subtotal'         => $subtotal,
                    ];
                }
                
                $taxAmount    = (float) ($request->tax_amount ?? 0);
                $discountVal  = (float) ($request->discount ?? 0);
                $taxType      = $request->tax_type ?? 'none';
                $discountType = $request->discount_type ?? 'fixed';
                $grandTotal   = ($totalAmount + $taxAmount) - $discountVal;
                $txDate = $request->transaction_date ? Carbon::parse($request->transaction_date) : $transaction->created_at;
                
                $transaction->update([
                    'customer_id'      => $request->customer_id,
                    'customer_name'    => $request->customer_name,
                    'customer_phone'   => $request->customer_phone,
                    'notes'            => $request->notes,
                    'total_amount'     => $grandTotal,
                    'discount'         => $discountVal,
                    'discount_type'    => $discountType,
                    'tax_type'         => $taxType,
                    'tax_amount'       => $taxAmount,
                    'payment_status'   => $request->payment_status,
                    'payment_method'   => $request->payment_method,
                    'created_at'       => $txDate,
                ]);
                
                // Hapus items lama
                TransactionItem::where('transaction_id', $id)->delete();
                
                // Buat items baru
                foreach ($itemsToCreate as $itemData) {
                    $itemData['transaction_id'] = $transaction->id;
                    TransactionItem::create($itemData);
                }
            });
            
            return redirect()->route('admin.transactions.show', $transaction->id)
                ->with('message', 'Transaksi berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $transaction = Transaction::with('items.product')->findOrFail($id);
        try {
            DB::transaction(function () use ($transaction) {
                // Kembalikan stok jika statusnya sudah lunas atau kredit (DP)
                if (in_array($transaction->payment_status, ['paid', 'credit'])) {
                    foreach ($transaction->items as $item) {
                        if ($item->product_batch_id) {
                            $batch = ProductBatch::find($item->product_batch_id);
                            if ($batch) {
                                $batch->increment('qty', $item->qty);
                                if ($batch->product) {
                                    $batch->product->increment('stock', $item->qty);
                                }
                            }
                        }
                    }
                }

                // Hapus items (sudah cascade di DB biasanya, tapi amannya hapus manual)
                $transaction->items()->delete();
                
                // Hapus payments terkait
                if (method_exists($transaction, 'payments')) {
                    $transaction->payments()->delete();
                }

                $transaction->delete();
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}