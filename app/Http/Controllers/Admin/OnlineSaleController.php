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
use Yajra\DataTables\Facades\DataTables;

class OnlineSaleController extends Controller
{
    public function create()
    {
        $customers = \App\Models\Customer::orderBy('name')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();
        $merek = \App\Models\Merek::orderBy('name', 'asc')->get();
        $categories = \App\Models\Category::orderBy('name', 'asc')->get();
        $affiliates = \App\Models\Affiliate::where('is_active', true)->get();
        $channels = \App\Models\ChannelSetting::where('slug', '!=', 'offline')->get();
        
        $mainWarehouse = \App\Models\Warehouse::where('type', 'main')->first();
        $mainWarehouseId = $mainWarehouse ? $mainWarehouse->id : 1;

        $batches = ProductBatch::with(['product.merek', 'variant'])
            ->where('warehouse_id', $mainWarehouseId)
            ->where('qty', '>', 0)
            ->whereHas('product', fn($q) => $q->where('status', 'Y'))
            ->get();

        $batchList = [];
        foreach ($batches as $batch) {
            $product = $batch->product;
            $batchList[] = [
                'id' => $batch->id,
                'product_id' => $product->id,
                'text' => ($product->merek->name ?? '') . ' ' . $product->name . ' (' . $batch->batch_no . ')',
                'price' => $batch->variant ? (int)$batch->variant->price : (int)$product->price,
                'stock' => $batch->qty
            ];
        }

        $posRoutes = [
            'products' => route('admin.pos.products'),
            'store' => route('admin.online_sale.store'), // Direct to OnlineSale store
            'receipt' => url('admin/pos/receipt'),
            'verify_voucher' => route('admin.pos.verify-voucher'),
        ];

        return view('admin.online_sale.pos_mode', compact('customers', 'batchList', 'categories', 'merek', 'posRoutes', 'affiliates', 'warehouses', 'channels'))->with('sb', 'OnlineSale');
    }

    public function store(Request $request)
    {
        if (is_string($request->items)) {
            $request->merge(['items' => json_decode($request->items, true)]);
        }

        $validator = Validator::make($request->all(), [
            'source' => 'required|exists:channel_settings,slug',
            'transaction_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|integer|min:1',
            'payment_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
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

            $transaction = DB::transaction(function () use ($request, $receiptPath) {
                $totalAmount = 0;
                $itemsToCreate = [];

                foreach ($request->items as $item) {
                    $batch = ProductBatch::with(['product', 'variant'])->findOrFail($item['batch_id']);
                    $product = $batch->product;

                    if ($batch->qty < $item['qty']) {
                        throw new \Exception('Stok batch ' . $batch->batch_no . ' untuk produk ' . $product->name . ' tidak mencukupi');
                    }

                    $price = (int)($batch->variant->price ?? $product->price);
                    $subtotal = $price * $item['qty'];
                    $totalAmount += $subtotal;

                    $itemsToCreate[] = [
                        'product_id' => $product->id,
                        'product_batch_id' => $batch->id,
                        'buy_price' => $batch->buy_price,
                        'qty' => $item['qty'],
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ];
                }

                $transaction = Transaction::create([
                    'user_id' => auth()->id(),
                    'source' => $request->source,
                    'notes' => $request->notes,
                    'total_amount' => $totalAmount,
                    'payment_status' => 'paid',
                    'payment_method' => 'transfer',
                    'delivery_type' => 'delivery',
                    'delivery_desc' => 'Online Marketplace Sale',
                    'midtrans_order_id' => 'MARKET-' . strtoupper($request->source) . '-' . uniqid(),
                    'payment_receipt' => $receiptPath,
                    'created_at' => $request->transaction_date ? \Carbon\Carbon::parse($request->transaction_date) : now(),
                ]);

                // Generate invoice number
                $transaction->update([
                    'invoice_number' => InvoiceService::generate(\Carbon\Carbon::parse($transaction->created_at)),
                ]);

                foreach ($itemsToCreate as $itemData) {
                    $itemData['transaction_id'] = $transaction->id;
                    TransactionItem::create($itemData);

                    // Deduct stock
                    ProductBatch::where('id', $itemData['product_batch_id'])->decrement('qty', $itemData['qty']);
                    Product::where('id', $itemData['product_id'])->decrement('stock', $itemData['qty']);
                }

                return $transaction;
            });

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi online berhasil dicatat',
                    'transaction_id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number
                ]);
            }

            return redirect()->route('admin.online_sale.index')->with('message', 'Transaksi online berhasil dicatat');
        }
        catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
    public function index()
    {
        return view('admin.online_sale.history')->with('sb', 'OnlineSale');
    }

    public function getall(Request $request)
    {
        $query = Transaction::where('source', '!=', 'offline')
            ->with(['items.product', 'items.batch']);

        if ($request->has('source') && !empty($request->source)) {
            $query->where('source', $request->source);
        }

        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->latest();

        return DataTables::of($transactions)
            ->addIndexColumn()
            ->editColumn('created_at', function ($trx) {
                return $trx->created_at->format('d/m/Y H:i');
            })
            ->editColumn('source', function ($trx) {
                if ($trx->source == 'shopee') return '<span class="badge badge-warning">Shopee</span>';
                if ($trx->source == 'tokopedia') return '<span class="badge badge-success">Tokopedia</span>';
                if ($trx->source == 'tiktok') return '<span class="badge badge-dark">TikTok</span>';
                return '<span class="badge badge-info">' . ucfirst($trx->source) . '</span>';
            })
            ->addColumn('notes_with_receipt', function ($trx) {
                $html = $trx->notes ?? '-';
                if ($trx->payment_receipt) {
                    $html .= '<br><a href="' . asset($trx->payment_receipt) . '" target="_blank" class="badge badge-info mt-1"><i class="fas fa-file-invoice"></i> Bukti Bayar</a>';
                }
                return $html;
            })
            ->addColumn('total_items', function ($trx) {
                return $trx->items->sum('qty') . ' Item';
            })
            ->editColumn('total_amount', function ($trx) {
                return 'Rp' . number_format($trx->total_amount, 0, ',', '.');
            })
            ->addColumn('action', function ($trx) {
                $itemsJson = htmlspecialchars(json_encode($trx->items), ENT_QUOTES, 'UTF-8');
                $receiptUrl = $trx->payment_receipt ? asset($trx->payment_receipt) : '';
                
                $html = '<div class="dropdown d-inline">
                            <button class="btn btn-primary dropdown-toggle btn-sm" type="button" data-toggle="dropdown">Aksi</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item has-icon show-detail" href="#" 
                                   data-items=\'' . $itemsJson . '\'
                                   data-receipt="' . $receiptUrl . '">
                                    <i class="fas fa-eye text-info"></i> Detail
                                </a>';
                if ($trx->payment_receipt) {
                    $html .= '<a class="dropdown-item has-icon" href="' . $receiptUrl . '" target="_blank">
                                <i class="fas fa-file-download text-success"></i> Lihat Bukti
                              </a>';
                }
                $html .= '<a class="dropdown-item has-icon" href="' . route('admin.online_sale.edit', $trx->id) . '">
                            <i class="fas fa-edit text-primary"></i> Edit
                          </a>
                          <div class="dropdown-divider"></div>
                          <a class="dropdown-item has-icon text-danger btn-delete" href="#" data-id="' . $trx->id . '">
                            <i class="fas fa-trash"></i> Hapus
                          </a>
                        </div>
                    </div>';
                return $html;
            })
            ->rawColumns(['source', 'notes_with_receipt', 'action'])
            ->make(true);
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
            
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Transaksi berhasil dihapus']);
            }
            return redirect()->route('admin.online_sale.index')->with('message', 'Transaksi berhasil dihapus dan stok dikembalikan');
        }
        catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
