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
        return view('admin.pos.index', compact('categories', 'posRoutes', 'affiliates'))->with('sb', 'POS');
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
                $channelSlug = $this->getPosChannel();

                $affiliateFeeTotal = 0;
            $affiliateFeeMode = $request->affiliate_fee_mode ?? 'ADD_TO_PRICE'; // Default to ADD_TO_PRICE
            $affiliate = null;
            $affiliateRates = collect([]);
            $affiliateId = null;

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
                            // Nominal global fee is usually per transaction or per item? 
                            // Standard interpretation: Nominal global is usually per transaction, but for per-item loop logic, it is ambiguous.
                            // However, in previous implementation, nominal was just added once at the end?
                            // Let's stick to previous logical interpretation or improve.
                            // If Global Nominal is 5000, usually it means 5000 per transaction, not per item.
                            // BUT specific product nominal (e.g. 5000 per unit sold) is clearly per unit.
                            
                            // Let's refine:
                            // Specific Percent/Nominal -> Per Unit.
                            // Global Percent -> Per Unit.
                            // Global Nominal -> One time per transaction.
                            
                            // So inside loop:
                            // If specific rate exists -> Calculate item fee * qty.
                            // If specific rate NOT exists -> 
                            //    If Global Percent -> Calculate item fee * qty.
                            //    If Global Nominal -> Do nothing here (handled outside).
                        }
                    }
                }
                
                // If ADD_TO_PRICE, increase final price
                if ($affiliate && $affiliateFeeMode == 'ADD_TO_PRICE') {
                    // Note: If Global Nominal, we usually add it to the TOTAL, not spread on items.
                    // But for Specific/Global Percent, we increase item price.
                    
                    // Logic:
                    // If rate was specific OR global percent:
                    if (isset($rate) || ($affiliate->fee_method == 'percent' && !$rate)) { // Only apply global percent if no specific rate
                         $finalPrice += $itemFee;
                    }
                }

                $subtotal = $finalPrice * $qty;
                $totalAmount += $subtotal;
                
                // Track total fee
                // If specific OR global percent: fee is per unit * qty
                if ($affiliate) {
                     if (isset($rate) || ($affiliate->fee_method == 'percent' && !$rate)) { // Only apply global percent if no specific rate
                         $affiliateFeeTotal += ($itemFee * $qty);
                     }
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
            
            // Handle Global Nominal Fee (Once per transaction)
            if ($affiliate && $affiliate->fee_method == 'nominal' && $affiliateRates->isEmpty()) { // Only apply global nominal if no specific rates defined
                // If using global nominal, we add it ONCE.
                // But does it apply if we have specific items?
                // Usually: Specific items get specific rules. Remaining items get global rule.
                // If Global is Nominal (e.g. 5000 per transaction), it applies regardless of specific items?
                // Providing simple logic: Global Nominal + Sum(Specific Fees).
                
                $affiliateFeeTotal += $affiliate->fee_value;
                if ($affiliateFeeMode == 'ADD_TO_PRICE') {
                     $totalAmount += $affiliate->fee_value;
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

                // Affiliate Logic
                $affiliateFee = 0;
                $affiliateId = null;
                $affiliateMode = null;
                
                if ($request->affiliate_id) {
                    $affiliate = \App\Models\Affiliate::find($request->affiliate_id);
                    if ($affiliate && $affiliate->is_active) {
                        $affiliateId = $affiliate->id;
                        $affiliateMode = $request->affiliate_fee_mode ?? 'ADD_TO_PRICE';
                        
                        // Calculate Fee
                        if ($affiliate->fee_method === 'percent') {
                            $affiliateFee = ($totalAmount - $finalDiscount) * ($affiliate->fee_value / 100);
                        } else {
                            $affiliateFee = $affiliate->fee_value;
                        }

                        // Apply Mode
                        if ($affiliateMode === 'ADD_TO_PRICE') {
                            // Fee is added on top of the customer's total (Customer pays)
                            // $totalAmount += $affiliateFee;  <-- Logic: Base + Fee
                        } else {
                            // FROM_MARGIN: Customer pays same amount, fee is just recorded (Seller pays)
                            // Fee doesn't affect total_amount stored in transaction as 'total_amount' is usually what customer pays
                        }
                    }
                }

                // Final Total Calculation
                // Base - Discount + (Fee if ADD_TO_PRICE)
                $finalTotal = $totalAmount - $finalDiscount;
                if ($affiliateMode === 'ADD_TO_PRICE') {
                    $finalTotal += $affiliateFee;
                }

                $transaction = Transaction::create([
                    'user_id' => auth()->id(),
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'customer_email' => $request->customer_email,
                    'source' => $channelSlug,
                    'notes' => $request->notes,
                    'total_amount' => $totalAmount - $finalDiscount,
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

    public function printReceipt($id)
    {
        $transaction = Transaction::with(['items.product', 'user'])->findOrFail($id);
        return view('admin.pos.receipt', compact('transaction'));
    }
}
