<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Services\InvoiceService;

class OnlineSaleController extends Controller
{
    public function create()
    {
        $products = Product::where('status', 'Y')
            ->orderBy('name', 'asc')
            ->with(['batches' => function ($q) {
            $q->orderBy('batch_no', 'asc');
        }])
            ->get();

        $channels = \App\Models\ChannelSetting::all();

        $batchList = [];
        foreach ($products as $product) {
            foreach ($product->batches as $batch) {
                if ($batch->qty > 0) {
                    // Load relasi untuk mendapatkan data lengkap
                    $batch->load(['variant.netto', 'product.merek']);
                    
                    $variant = $batch->variant;
                    $netto = $variant ? $variant->netto : null;
                    
                    $merekName = ($product && $product->merek) ? trim($product->merek->name) : '';
                    $productName = trim($product->name ?? '');
                    $nettoValue = $netto ? trim($netto->netto_value ?? '') : '';
                    $satuan = $netto ? trim($netto->satuan ?? '') : '';
                    $batchNo = trim($batch->batch_no ?? '');
                    $stock = $batch->qty;
                    $expiredDate = $batch->expiry_date ? $batch->expiry_date->format('d/m/Y') : 'No Exp';
                    
                    // Format: Merek + Produk + Netto + Satuan + (batch + stok + Expired date)
                    $parts = array_filter([$merekName, $productName, $nettoValue, $satuan]);
                    $labelText = implode(' ', $parts);
                    $fullText = $labelText . ' (' . $batchNo . ' - Stok: ' . $stock . ' - Exp: ' . $expiredDate . ')';

                    $prices = [
                        'offline' => \App\Services\PricingService::calculate($batch, 'offline'),
                    ];

                    foreach ($channels as $channel) {
                        $prices[$channel->slug] = \App\Services\PricingService::calculate($batch, $channel->slug);
                    }

                    $batchList[] = (object)[
                        'id' => $batch->id,
                        'product_id' => $product->id,
                        'text' => $fullText,
                        'stock' => $batch->qty,
                        'prices' => $prices
                    ];
                }
            }
        }

        return view('admin.online_sale.index', compact('products', 'batchList', 'channels'))->with('sb', 'OnlineSale');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source' => 'required|exists:channel_settings,slug',
            'transaction_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|integer|min:1',
            'payment_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $receiptPath = null;
            if ($request->hasFile('payment_receipt')) {
                $file = $request->file('payment_receipt');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/receipts'), $filename);
                $receiptPath = 'uploads/receipts/' . $filename;
            }

            DB::transaction(function () use ($request, $receiptPath) {
                $totalAmount = 0;
                $itemsToCreate = [];

                foreach ($request->items as $item) {
                    $batch = ProductBatch::with('product')->findOrFail($item['product_batch_id']);
                    $product = $batch->product;

                    if ($batch->qty < $item['qty']) {
                        throw new \Exception('Stok batch ' . $batch->batch_no . ' untuk produk ' . $product->name . ' tidak mencukupi (Tersisa: ' . $batch->qty . ')');
                    }

                    $subtotal = $product->price * $item['qty'];
                    $totalAmount += $subtotal;

                    $itemsToCreate[] = [
                        'product_id' => $product->id,
                        'product_batch_id' => $batch->id,
                        'buy_price' => $batch->buy_price,
                        'qty' => $item['qty'],
                        'price' => $product->price,
                        'subtotal' => $subtotal,
                    ];
                }

                $transaction = Transaction::create([
                    'user_id' => auth()->id(),
                    'source' => $request->source,
                    'notes' => $request->notes,
                    'total_amount' => $totalAmount,
                    'payment_status' => 'paid',
                    'delivery_type' => 'delivery',
                    'delivery_desc' => 'Online Marketplace Sale',
                    'midtrans_order_id' => 'MARKET-' . strtoupper($request->source) . '-' . uniqid(),
                    'payment_receipt' => $receiptPath,
                    'created_at' => $request->transaction_date ?? now(),
                ]);

                // Generate invoice number
                $transaction->update([
                    'invoice_number' => InvoiceService::generate(
                    \Carbon\Carbon::parse($transaction->created_at)
                ),
                ]);

                foreach ($itemsToCreate as $itemData) {
                    $itemData['transaction_id'] = $transaction->id;
                    TransactionItem::create($itemData);

                    // Deduct batch stock
                    ProductBatch::where('id', $itemData['product_batch_id'])->decrement('qty', $itemData['qty']);

                    // Deduct global stock
                    Product::where('id', $itemData['product_id'])->decrement('stock', $itemData['qty']);
                }
            });

            return redirect()->route('admin.online_sale.index')->with('message', 'Transaksi online berhasil dicatat');
        }
        catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
    public function index()
    {
        $transactions = Transaction::where('source', '!=', 'offline')
            ->with(['items.product', 'items.batch'])
            ->latest()
            ->get();
        return view('admin.online_sale.history', compact('transactions'))->with('sb', 'OnlineSale');
    }

    public function edit($id)
    {
        $transaction = Transaction::where('source', '!=', 'offline')->with('items.product', 'items.batch')->findOrFail($id);

        // Reuse product list logic from index
        $products = Product::where('status', 'Y')
            ->orderBy('name', 'asc')
            ->with(['batches' => function ($q) {
            $q->orderBy('batch_no', 'asc');
        }])
            ->get();

        $channels = \App\Models\ChannelSetting::all();

        $batchList = [];
        foreach ($products as $product) {
            foreach ($product->batches as $batch) {
                // For edit, we include the batch even if qty is 0 because the current transaction might have it
                
                // Load relasi untuk mendapatkan data lengkap
                $batch->load(['variant.netto', 'product.merek']);
                
                $variant = $batch->variant;
                $netto = $variant ? $variant->netto : null;
                
                $merekName = ($product && $product->merek) ? trim($product->merek->name) : '';
                $productName = trim($product->name ?? '');
                $nettoValue = $netto ? trim($netto->netto_value ?? '') : '';
                $satuan = $netto ? trim($netto->satuan ?? '') : '';
                $batchNo = trim($batch->batch_no ?? '');
                $stock = $batch->qty;
                $expiredDate = $batch->expiry_date ? $batch->expiry_date->format('d/m/Y') : 'No Exp';
                
                // Format: Merek + Produk + Netto + Satuan + (batch + stok + Expired date)
                $parts = array_filter([$merekName, $productName, $nettoValue, $satuan]);
                $labelText = implode(' ', $parts);
                $fullText = $labelText . ' (' . $batchNo . ' - Stok: ' . $stock . ' - Exp: ' . $expiredDate . ')';

                $prices = [
                    'offline' => \App\Services\PricingService::calculate($batch, 'offline'),
                ];

                foreach ($channels as $channel) {
                    $prices[$channel->slug] = \App\Services\PricingService::calculate($batch, $channel->slug);
                }

                $batchList[] = (object)[
                    'id' => $batch->id,
                    'product_id' => $product->id,
                    'text' => $fullText,
                    'stock' => $batch->qty,
                    'prices' => $prices
                ];
            }
        }

        return view('admin.online_sale.edit', compact('transaction', 'products', 'batchList', 'channels'))->with('sb', 'OnlineSale');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'source' => 'required|exists:channel_settings,slug',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|integer|min:1',
            'payment_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $transaction = Transaction::where('source', '!=', 'offline')->with('items')->findOrFail($id);

        try {
            $receiptPath = $transaction->payment_receipt;
            if ($request->hasFile('payment_receipt')) {
                // Delete old file
                if ($receiptPath && File::exists(public_path($receiptPath))) {
                    File::delete(public_path($receiptPath));
                }

                $file = $request->file('payment_receipt');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/receipts'), $filename);
                $receiptPath = 'uploads/receipts/' . $filename;
            }

            DB::transaction(function () use ($request, $transaction, $receiptPath) {
                // 1. Revert Stock
                foreach ($transaction->items as $oldItem) {
                    ProductBatch::where('id', $oldItem->product_batch_id)->increment('qty', $oldItem->qty);
                    Product::where('id', $oldItem->product_id)->increment('stock', $oldItem->qty);
                }

                // 2. Clear old items
                $transaction->items()->delete();

                // 3. Process new items
                $totalAmount = 0;
                foreach ($request->items as $item) {
                    $batch = ProductBatch::with('product')->findOrFail($item['product_batch_id']);
                    $product = $batch->product;

                    if ($batch->qty < $item['qty']) {
                        throw new \Exception('Stok batch ' . $batch->batch_no . ' untuk produk ' . $product->name . ' tidak mencukupi (Tersisa: ' . $batch->qty . ')');
                    }

                    $subtotal = $product->price * $item['qty'];
                    $totalAmount += $subtotal;

                    $transaction->items()->create([
                        'product_id' => $product->id,
                        'product_batch_id' => $batch->id,
                        'buy_price' => $batch->buy_price,
                        'qty' => $item['qty'],
                        'price' => $product->price,
                        'subtotal' => $subtotal,
                    ]);

                    // Deduct stock
                    $batch->decrement('qty', $item['qty']);
                    $product->decrement('stock', $item['qty']);
                }

                // 4. Update Transaction
                $transaction->update([
                    'source' => $request->source,
                    'notes' => $request->notes,
                    'total_amount' => $totalAmount,
                    'payment_receipt' => $receiptPath,
                    'created_at' => $request->transaction_date,
                ]);
            });

            return redirect()->route('admin.online_sale.index')->with('message', 'Transaksi online berhasil diperbarui');
        }
        catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $transaction = Transaction::where('source', '!=', 'offline')->with('items')->findOrFail($id);

        try {
            DB::transaction(function () use ($transaction) {
                // Revert stock
                foreach ($transaction->items as $item) {
                    ProductBatch::where('id', $item->product_batch_id)->increment('qty', $item->qty);
                    Product::where('id', $item->product_id)->increment('stock', $item->qty);
                }

                $transaction->items()->delete();
                $transaction->delete();
            });

            return redirect()->route('admin.online_sale.index')->with('message', 'Transaksi berhasil dihapus dan stok dikembalikan');
        }
        catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
