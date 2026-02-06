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

class OnlineSaleController extends Controller
{
    public function index()
    {
        $products = Product::where('status', 'Y')
            ->orderBy('name', 'asc')
            ->with(['batches' => function($q) {
                $q->orderBy('batch_no', 'asc');
            }])
            ->get();

        $channels = \App\Models\ChannelSetting::all();

        $batchList = [];
        foreach ($products as $product) {
            foreach ($product->batches as $batch) {
                if ($batch->qty > 0) {
                    $prices = [
                        'offline' => \App\Services\PricingService::calculate($batch, 'offline'),
                    ];

                    foreach ($channels as $channel) {
                        $prices[$channel->slug] = \App\Services\PricingService::calculate($batch, $channel->slug);
                    }

                    $batchList[] = (object)[
                        'id' => $batch->id,
                        'product_id' => $product->id,
                        'text' => "{$product->name} (Batch: {$batch->batch_no}) (Stok: {$batch->qty})",
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::transaction(function() use ($request) {
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
                    'created_at' => $request->transaction_date ?? now(),
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

            return redirect()->route('admin.online_sale.history')->with('message', 'Transaksi online berhasil dicatat');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
    public function history()
    {
        $transactions = Transaction::where('source', '!=', 'offline')
            ->with(['items.product', 'items.batch'])
            ->latest()
            ->get();
        return view('admin.online_sale.history', compact('transactions'))->with('sb', 'OnlineSaleHistory');
    }
}
