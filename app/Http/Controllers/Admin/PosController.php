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
        $affiliates = \App\Models\Affiliate::where('is_active', true)->orderBy('name')->get();
        return view('admin.pos.index', compact('categories', 'posRoutes', 'affiliates', 'isSales'))->with('sb', 'POS');
    }

    private function getPosChannel()
    {
        // Priority: specific 'offline' -> 'offline-store' -> first available
        $channel = \App\Models\ChannelSetting::where('slug', 'offline')->first();
        if ($channel) return $channel->slug;

        $channel = \App\Models\ChannelSetting::where('slug', 'offline-store')->first();
        if ($channel) return $channel->slug;

        $channel = \App\Models\ChannelSetting::first();
        return $channel ? $channel->slug : 'offline';
    }

    public function fetchProducts(Request $request)
    {
        $channelSlug = $this->getPosChannel();

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
            ->map(function($product) use ($channelSlug) {
                // Strictly prioritize database price_real as requested. 
                // Fallback to price then to calculation if price_real is not set.
                if ($product->price_real > 0) {
                    $product->offline_price = $product->price_real;
                } elseif ($product->price > 0) {
                    $product->offline_price = $product->price;
                } else {
                    $product->offline_price = PricingService::calculateForProduct($product, $channelSlug);
                }
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
                $affiliateFeeTotal = 0;
                $channelSlug = $this->getPosChannel();
                
                $affiliateFeeMode = $request->affiliate_fee_mode ?? 'ADD_TO_PRICE'; 
                $affiliate = null;
                $affiliateRates = collect([]);

            if ($request->affiliate_id) {
                $affiliate = \App\Models\Affiliate::find($request->affiliate_id);
                if ($affiliate && $affiliate->is_active) {
                    $affiliateId = $affiliate->id;
                    $affiliateRates = \App\Models\AffiliateProductCommission::where('affiliate_id', $affiliate->id)
                        ->get()
                        ->keyBy('product_id');
                } else {
                    $affiliate = null; // Invalidate if not active
                }
            }

            foreach ($request->items as $item) {
                $batch = ProductBatch::with('product')->findOrFail($item['batch_id']);
                $product = $batch->product;
                $qty = $item['qty'];

                if ($batch->qty < $qty) {
                    throw new \Exception("Stok batch {$batch->batch_no} untuk produk {$product->name} tidak mencukupi.");
                }

                // Strictly prioritize database price_real as requested.
                if ($product->price_real > 0) {
                    $basePrice = $product->price_real;
                } elseif ($product->price > 0) {
                    $basePrice = $product->price;
                } else {
                    $basePrice = PricingService::calculate($batch, $channelSlug);
                }
                
                $finalPrice = $basePrice;

                // Calculate Affiliate Fee for this item
                $itemFee = 0;
                if ($affiliate) {
                    // Check specific rate
                    $rate = $affiliateRates->get($product->id);
                    if ($rate) {
                        if ($rate->fee_method == 'percent') {
                            $itemFee = $basePrice * ($rate->fee_value / 100);
                        } else {
                            $itemFee = $rate->fee_value;
                        }
                    } else {
                        // Global rate
                        if ($affiliate->fee_method == 'percent') {
                            $itemFee = $basePrice * ($affiliate->fee_value / 100);
                        } else {
                            // Global nominal per unit interpretation
                            $itemFee = $affiliate->fee_value;
                        }
                    }
                }
                
                // If ADD_TO_PRICE, increase final price
                if ($affiliate && $affiliateFeeMode == 'ADD_TO_PRICE') {
                    $finalPrice += $itemFee;
                }

                $subtotal = $finalPrice * $qty;
                $totalAmount += $subtotal;
                
                if ($affiliate) {
                    $affiliateFeeTotal += ($itemFee * $qty);
                }

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_batch_id' => $batch->id,
                    'buy_price' => $batch->buy_price,
                    'qty' => $qty,
                    'price' => $finalPrice, // Price saved includes fee if ADD_TO_PRICE
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
                    $voucher = Voucher::where('code', $request->voucher_code)
                        ->where('status', 'ACTIVE')
                        ->first();
                        
                    if ($voucher) {
                        // Check date range
                        $now = now();
                        if (($voucher->start_date && $now->lt($voucher->start_date)) || 
                            ($voucher->end_date && $now->gt($voucher->end_date))) {
                            throw new \Exception("Voucher tidak dapat digunakan saat ini.");
                        }

                        // Check usage limit
                        if ($voucher->usage_limit && $voucher->usage_count >= $voucher->usage_limit) {
                            throw new \Exception("Voucher telah mencapai batas penggunaan.");
                        }

                        $eligibleTotal = 0;
                        if ($voucher->applies_to_all) {
                            $eligibleTotal = $totalAmount;
                        } else {
                            $voucherProductIds = $voucher->products()->pluck('products.id')->toArray();
                            foreach ($itemsToCreate as $item) {
                                if (in_array($item['product_id'], $voucherProductIds)) {
                                    $eligibleTotal += $item['subtotal'];
                                }
                            }
                        }

                        if ($eligibleTotal > 0) {
                            if ($voucher->discount_type == 'PERCENT') {
                                $voucherDiscount = ($eligibleTotal * $voucher->percent / 100);
                            } else {
                                $voucherDiscount = min($eligibleTotal, $voucher->nominal);
                            }
                            $finalDiscount += $voucherDiscount;
                            
                            // Increment usage count
                            $voucher->increment('usage_count');
                        } else {
                            // If user provided a code but no products are eligible, 
                            // we can either throw exception or just not apply.
                            // User request implies "if products determined, then only those".
                            // So if none in cart match, discount is 0.
                        }
                    }
                }

                // Final Total Calculation
                // Base - Discount + (Fee if ADD_TO_PRICE)
                $finalTotal = $totalAmount - $finalDiscount;

                $transaction = Transaction::create([
                    'user_id' => auth()->id(),
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'customer_email' => $request->customer_email,
                    'source' => $channelSlug,
                    'notes' => $request->notes,
                    'total_amount' => $finalTotal,
                    'payment_status' => $request->payment_status,
                    'payment_method' => $request->payment_method,
                    'delivery_type' => 'pickup',
                    'delivery_desc' => 'POS Offline Sale',
                    'midtrans_order_id' => 'POS-' . strtoupper(Str::random(10)),
                    'voucher_code' => $request->voucher_code,
                    'discount' => $finalDiscount,
                    'affiliate_id' => $affiliate ? $affiliate->id : null,
                    'affiliate_fee_total' => $affiliateFeeTotal,
                    'affiliate_fee_mode' => $request->affiliate_fee_mode,
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

    public function verifyVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Format tidak valid'], 422);
        }

        $voucher = Voucher::where('code', $request->code)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Voucher tidak ditemukan atau tidak aktif'], 404);
        }

        // Check date range
        $now = now();
        if (($voucher->start_date && $now->lt($voucher->start_date)) || 
            ($voucher->end_date && $now->gt($voucher->end_date))) {
            return response()->json(['success' => false, 'message' => 'Voucher tidak dapat digunakan saat ini'], 400);
        }

        // Check usage limit
        if ($voucher->usage_limit && $voucher->usage_count >= $voucher->usage_limit) {
            return response()->json(['success' => false, 'message' => 'Voucher telah mencapai batas penggunaan'], 400);
        }

        $eligibleTotal = 0;
        if ($voucher->applies_to_all) {
            foreach ($request->items as $item) {
                $eligibleTotal += $item['subtotal'];
            }
        } else {
            $voucherProductIds = $voucher->products()->pluck('products.id')->toArray();
            foreach ($request->items as $item) {
                if (in_array($item['product_id'], $voucherProductIds)) {
                    $eligibleTotal += $item['subtotal'];
                }
            }
        }

        if ($eligibleTotal <= 0) {
            return response()->json(['success' => false, 'message' => 'Voucher tidak berlaku untuk produk di keranjang anda'], 400);
        }

        $discount = 0;
        if ($voucher->discount_type == 'PERCENT') {
            $discount = ($eligibleTotal * $voucher->percent / 100);
        } else {
            $discount = min($eligibleTotal, $voucher->nominal);
        }

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'code' => $voucher->code,
            'name' => $voucher->name
        ]);
    }

    public function printReceipt($id)
    {
        $transaction = Transaction::with(['items.product', 'user'])->findOrFail($id);
        return view('admin.pos.receipt', compact('transaction'));
    }
}
