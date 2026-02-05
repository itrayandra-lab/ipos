<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Voucher;
use App\Services\PricingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name', 'asc')->get();
        $isSales = auth()->user()->isSales();
        $posRoutes = [
            'products' => route($isSales ? 'sales.pos.products' : 'admin.pos.products'),
            'store' => route($isSales ? 'sales.pos.store' : 'admin.pos.store'),
            'receipt' => url($isSales ? 'sales/pos/receipt' : 'admin/pos/receipt'),
        ];
        return view('admin.pos.index', compact('categories', 'posRoutes'))->with('sb', 'POS');
    }

    public function fetchProducts(Request $request)
    {
        $query = Product::with(['photos', 'category', 'batches' => function($q) {
            $q->where('qty', '>', 0)->orderBy('id', 'asc');
        }]);

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->where('status', 'Y')
            ->get()
            ->map(function($product) {
                $product->offline_price = PricingService::calculateForProduct($product, 'offline');
                return $product;
            })
            ->filter(function($product) {
                // Only show if has stock AND has a valid price
                return $product->batches->sum('qty') > 0 && $product->offline_price > 0;
            })
            ->values();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'payment_method' => 'required|in:cash,qris,transfer,debit',
            'payment_status' => 'required|in:draft,unpaid,paid,canceled',
            'notes' => 'nullable|string',
            'discount_manual' => 'nullable|numeric|min:0',
            'voucher_code' => 'nullable|string|exists:vouchers,code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            return DB::transaction(function() use ($request) {
                $totalAmount = 0;
                $itemsToCreate = [];

                foreach ($request->items as $item) {
                    $batch = ProductBatch::with('product')->findOrFail($item['batch_id']);
                    $product = $batch->product;
                    $qty = $item['qty'];

                    if ($batch->qty < $qty) {
                        throw new \Exception("Stok batch {$batch->batch_no} untuk produk {$product->name} tidak mencukupi.");
                    }

                    $price = PricingService::calculate($batch, 'offline');
                    $subtotal = $price * $qty;
                    $totalAmount += $subtotal;

                    $itemsToCreate[] = [
                        'product_id' => $product->id,
                        'product_batch_id' => $batch->id,
                        'buy_price' => $batch->buy_price,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ];

                    if ($request->payment_status === 'paid') {
                        $batch->decrement('qty', $qty);
                        $product->decrement('stock', $qty);
                    }
                }

                // Apply discounts
                $finalDiscount = (float)($request->discount_manual ?? 0);
                if ($request->voucher_code) {
                    $voucher = Voucher::where('code', $request->voucher_code)->first();
                    if ($voucher) {
                        $voucherDiscount = ($totalAmount * $voucher->percent / 100);
                        $finalDiscount += $voucherDiscount;
                    }
                }

                $transaction = Transaction::create([
                    'user_id' => auth()->id(),
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'customer_email' => $request->customer_email,
                    'source' => 'offline',
                    'notes' => $request->notes,
                    'total_amount' => $totalAmount - $finalDiscount,
                    'payment_status' => $request->payment_status,
                    'payment_method' => $request->payment_method,
                    'delivery_type' => 'pickup',
                    'delivery_desc' => 'POS Offline Sale',
                    'midtrans_order_id' => 'POS-' . strtoupper(Str::random(10)),
                    'voucher_code' => $request->voucher_code,
                    'discount' => $finalDiscount,
                ]);

                foreach ($itemsToCreate as $itemData) {
                    $itemData['transaction_id'] = $transaction->id;
                    TransactionItem::create($itemData);
                }

                return response()->json([
                    'success' => true, 
                    'message' => 'Transaksi berhasil disimpan',
                    'transaction_id' => $transaction->id
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function printReceipt($id)
    {
        $transaction = Transaction::with(['items.product', 'user'])->findOrFail($id);
        return view('admin.pos.receipt', compact('transaction'));
    }
}
