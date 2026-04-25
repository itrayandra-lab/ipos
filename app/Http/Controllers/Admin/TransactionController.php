<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\TransactionItem;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\TransactionPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
        $counts = Transaction::select('payment_status', DB::raw('count(*) as total'))
            ->groupBy('payment_status')
            ->get()
            ->pluck('total', 'payment_status')
            ->toArray();

        return view('admin.transaction.index')->with([
            'sb' => 'Transaction',
            'counts' => $counts
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
                    <button type="button" class="btn btn-action-custom btn-sm dropdown-toggle" data-toggle="dropdown">
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
        $transaction = Transaction::with(['user', 'customer', 'items.product.merek', 'items.batch.variant', 'payments'])->findOrFail($id);
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

        // Add bundles to batchList
        $bundles = Product::where('is_bundle', true)->where('status', 'Y')->with('merek', 'variants')->get();
        foreach ($bundles as $bundle) {
            $merekName = $bundle->merek ? trim($bundle->merek->name) : '';
            $labelText = implode(' ', array_filter([$merekName, trim($bundle->name)]));
            
            $batchList[] = [
                'id'        => 'bundle-' . $bundle->id,
                'text'      => $labelText . ' (Bundling)',
                'price'     => $bundle->price > 0 ? $bundle->price : ($bundle->variants->first()->price ?? 0),
                'stock'     => 999,
                'buy_price' => 0,
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
            'items.*.product_batch_id' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $transaction, $id) {
                $totalAmount   = 0;
                $itemsToCreate = [];
                
                foreach ($request->items as $item) {
                    $isBundle = str_starts_with($item['product_batch_id'], 'bundle-');
                    $qty      = (int) $item['qty'];
                    $price    = (float) $item['price'];
                    $subtotal = $price * $qty;
                    $totalAmount += $subtotal;

                    if ($isBundle) {
                        $bundleId = str_replace('bundle-', '', $item['product_batch_id']);
                        $product = Product::findOrFail($bundleId);
                        $batchId = null;
                        $buyPrice = 0;
                    } else {
                        $batch    = ProductBatch::with('product')->findOrFail($item['product_batch_id']);
                        $product  = $batch->product;
                        $batchId = $batch->id;
                        $buyPrice = $batch->buy_price ?? 0;
                    }

                    $itemsToCreate[] = [
                        'product_id'       => $product->id,
                        'product_batch_id' => $batchId,
                        'buy_price'        => $buyPrice,
                        'qty'              => $qty,
                        'price'            => $price,
                        'subtotal'         => $subtotal,
                        'is_bundle_main'   => $isBundle
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
                $stockService = new \App\Services\StockService();
                foreach ($itemsToCreate as $itemData) {
                    $isBundleMain = $itemData['is_bundle_main'] ?? false;
                    unset($itemData['is_bundle_main']);

                    $itemData['transaction_id'] = $transaction->id;
                    $mainItem = TransactionItem::create($itemData);

                    if (in_array($request->payment_status, ['paid', 'credit']) && $isBundleMain) {
                        $stockService->explodeBundleComponents($itemData['product_id'], $itemData['qty'], $transaction->id, $mainItem->id);
                    }
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

    public function uploadReceipt(Request $request, $id)
    {
        try {
            $request->validate([
                'receipt'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'amount'       => 'required|numeric|min:0',
                'payment_date' => 'required|date',
                'notes'        => 'nullable|string',
            ]);
            $transaction = Transaction::findOrFail($id);
            $dbPath = null;
            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $filename = time() . '_' . $file->getClientOriginalName();
                $fullPath = public_path('uploads/receipts');
                if (!File::isDirectory($fullPath)) {
                    File::makeDirectory($fullPath, 0777, true, true);
                }
                $file->move($fullPath, $filename);
                $dbPath = 'uploads/receipts/' . $filename;
            }
            $payment = TransactionPayment::create([
                'transaction_id' => $transaction->id,
                'amount'         => $request->amount,
                'payment_date'   => $request->payment_date,
                'payment_method' => $transaction->payment_method ?: 'manual', 
                'payment_receipt' => $dbPath,
                'notes'          => $request->notes,
            ]);
            $totalPaid = $transaction->payments()->sum('amount');
            if ($totalPaid >= $transaction->total_amount) {
                $transaction->update(['payment_status' => 'paid']);
            }
            return redirect()->back()->with('message', 'Pembayaran berhasil dicatat.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    public function settlePayment(Request $request, $id)
    {
        $transaction = Transaction::with('items.batch.product')->findOrFail($id);
        if ($transaction->payment_status === 'paid') {
            return redirect()->back()->with('info', 'Transaksi sudah lunas.');
        }
        try {
            DB::transaction(function () use ($transaction, $request) {
                // Deduct stock if not already deducted (for credit/unpaid status)
                // Note: Logic here depends on when stock is deducted in other parts of the app
                if (!in_array($transaction->payment_status, ['paid', 'credit'])) {
                    foreach ($transaction->items as $item) {
                        if ($item->product_batch_id) {
                            $batch = $item->batch;
                            if ($batch) {
                                if ($batch->qty < $item->qty) {
                                    throw new \Exception("Stok batch {$batch->batch_no} untuk produk {$item->product->name} tidak mencukupi.");
                                }
                                $batch->decrement('qty', $item->qty);
                                $item->product->decrement('stock', $item->qty);
                            }
                        }
                    }
                }
                
                $paidSoFar = $transaction->payments()->sum('amount');
                $balance = $transaction->total_amount - $paidSoFar;
                $dbPath = null;
                if ($request->hasFile('receipt')) {
                    $file = $request->file('receipt');
                    $filename = time() . '_settle_' . $file->getClientOriginalName();
                    $fullPath = public_path('uploads/receipts');
                    if (!File::isDirectory($fullPath)) {
                        File::makeDirectory($fullPath, 0777, true, true);
                    }
                    $file->move($fullPath, $filename);
                    $dbPath = 'uploads/receipts/' . $filename;
                }
                TransactionPayment::create([
                    'transaction_id' => $transaction->id,
                    'amount'         => $balance,
                    'payment_date'   => Carbon::now(),
                    'payment_method' => $request->payment_method ?? $transaction->payment_method,
                    'payment_receipt' => $dbPath,
                    'notes'          => 'Pelunasan',
                ]);
                $transaction->update([
                    'payment_status' => 'paid',
                    'payment_method' => $request->payment_method ?? $transaction->payment_method,
                    'is_dp' => false,
                ]);
            });
            return redirect()->back()->with('message', 'Transaksi #' . $transaction->id . ' telah dilunasi.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melunasi transaksi: ' . $e->getMessage());
        }
    }

    public function updatePaymentReceipt(Request $request)
    {
        try {
            $request->validate([
                'payment_id' => 'required|exists:transaction_payments,id',
                'receipt'    => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            $payment = TransactionPayment::findOrFail($request->payment_id);
            
            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $filename = time() . '_update_' . $file->getClientOriginalName();
                $fullPath = public_path('uploads/receipts');
                if (!File::isDirectory($fullPath)) {
                    File::makeDirectory($fullPath, 0777, true, true);
                }
                $file->move($fullPath, $filename);
                $payment->update([
                    'payment_receipt' => 'uploads/receipts/' . $filename
                ]);
            }

            return redirect()->back()->with('message', 'Bukti pembayaran berhasil diupload.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal upload bukti: ' . $e->getMessage());
        }
    }

    public function quickUploadReceipt(Request $request, $id)
    {
        try {
            $request->validate([
                'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            $transaction = Transaction::findOrFail($id);
            
            // Check for existing payment without receipt
            $payment = TransactionPayment::where('transaction_id', $id)
                        ->whereNull('payment_receipt')
                        ->first();
            
            if (!$payment) {
                // If no payment record exists, create one for the full amount (since it's 'paid')
                $payment = TransactionPayment::create([
                    'transaction_id' => $transaction->id,
                    'amount'         => $transaction->total_amount,
                    'payment_date'   => $transaction->created_at,
                    'payment_method' => $transaction->payment_method ?: 'manual',
                    'notes'          => 'Otomatis via Upload Bukti (Lunas)',
                ]);
            }

            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $filename = time() . '_quick_' . $file->getClientOriginalName();
                $fullPath = public_path('uploads/receipts');
                if (!File::isDirectory($fullPath)) {
                    File::makeDirectory($fullPath, 0777, true, true);
                }
                $file->move($fullPath, $filename);
                $payment->update([
                    'payment_receipt' => 'uploads/receipts/' . $filename
                ]);
            }

            return redirect()->back()->with('message', 'Bukti pembayaran berhasil diupload.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal upload bukti: ' . $e->getMessage());
        }
    }
    public function productReport()
    {
        return view('admin.transaction.report_product')->with([
            'sb' => 'Transaction'
        ]);
    }

    public function productReportData(Request $request)
    {
        $query = DB::table('transaction_items')
            ->select(
                'merek.name as merek_name',
                'products.name as product_name',
                'product_variants.variant_name',
                'product_variants.sku_code',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_amount')
            )
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->leftJoin('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id');

        // Apply filters
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('transactions.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('transactions.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        // Bundling Logic: Show components, hide bundle parents
        $query->where(function($q) {
            $q->where('products.is_bundle', 0)
              ->orWhereNotNull('transaction_items.parent_item_id');
        });

        $query->groupBy('transaction_items.product_id', 'transaction_items.product_variant_id', 'merek.name', 'products.name', 'product_variants.variant_name', 'product_variants.sku_code')
              ->orderBy('total_qty', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('total_amount', function ($row) {
                return 'Rp ' . number_format($row->total_amount, 0, ',', '.');
            })
            ->make(true);
    }

    public function printProductReport(Request $request)
    {
        $query = DB::table('transaction_items')
            ->select(
                'merek.name as merek_name',
                'products.name as product_name',
                'product_variants.variant_name',
                'product_variants.sku_code',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_amount')
            )
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->leftJoin('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id');

        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('transactions.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('transactions.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        // Bundling Logic: Show components, hide bundle parents
        $query->where(function($q) {
            $q->where('products.is_bundle', 0)
              ->orWhereNotNull('transaction_items.parent_item_id');
        });

        $items = $query->groupBy('transaction_items.product_id', 'transaction_items.product_variant_id', 'merek.name', 'products.name', 'product_variants.variant_name', 'product_variants.sku_code')
              ->orderBy('total_qty', 'desc')
              ->get();

        return view('admin.transaction.print_product', compact('items'));
    }
}