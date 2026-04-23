<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Category;
use App\Models\Merek;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Voucher;
use App\Services\InvoiceService;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('name')->get();
        
        $mainWarehouse = \App\Models\Warehouse::where('type', 'main')->first();
        $mainWarehouseId = $mainWarehouse ? $mainWarehouse->id : 1;

        $batches = ProductBatch::with(['product.merek', 'variant'])
            ->where('warehouse_id', $mainWarehouseId)
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
                'price'     => $batch->variant ? (int)$batch->variant->price : 0,
                'stock'     => $batch->qty,
                'buy_price' => $batch->buy_price ?? 0,
                'product_id' => $product->id,
                'batch_no'  => $batch->batch_no,
            ];
        }

        $categories = Category::orderBy('name', 'asc')->get();
        $merek = Merek::orderBy('name', 'asc')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();
        $affiliates = \App\Models\Affiliate::where('is_active', true)->get();
        $isSales = auth()->user()->isSales();
        $posRoutes = [
            'products' => route($isSales ? 'sales.pos.products' : 'admin.pos.products'),
            'store' => route($isSales ? 'sales.pos.store' : 'admin.pos.store'),
            'receipt' => url($isSales ? 'sales/pos/receipt' : 'admin/pos/receipt'),
            'verify_voucher' => route($isSales ? 'sales.pos.verify-voucher' : 'admin.pos.verify-voucher'),
        ];
        return view('admin.pos.index', compact('customers', 'batchList', 'categories', 'merek', 'posRoutes', 'affiliates', 'isSales', 'warehouses'))->with('sb', 'POS');
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
        $mainWarehouse = \App\Models\Warehouse::where('type', 'main')->first();
        $defaultWarehouseId = $mainWarehouse ? $mainWarehouse->id : 1;
        $warehouseId = $request->warehouse_id ? (int)$request->warehouse_id : $defaultWarehouseId;

        // Query batches grouped by variant, only from the selected/main warehouse with stock > 0
        $batchQuery = ProductBatch::with([
                'product.merek',
                'product.photos',
                'product.category',
                'variant.netto',
            ])
            ->where('qty', '>', 0)
            ->where('warehouse_id', $warehouseId)
            ->whereHas('product', fn($q) => $q->where('status', 'Y'));

        // Apply search filter on product name or merek name
        if ($request->search) {
            $batchQuery->whereHas('product', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('merek', fn($mq) => $mq->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->merek_id) {
            $batchQuery->whereHas('product', fn($q) => $q->where('merek_id', $request->merek_id));
        }

        $batches = $batchQuery->orderBy('id', 'asc')->get();

        // Group batches by variant_id (or product_id if no variant)
        $variantGroups = [];
        foreach ($batches as $batch) {
            $product = $batch->product;
            $variant = $batch->variant;

            // Unique key per variant (or per product if no variant)
            $groupKey = $variant ? 'v_' . $variant->id : 'p_' . $product->id;

            if (!isset($variantGroups[$groupKey])) {
                // Build display name: MerekName + ProductName only (no netto)
                $merekName   = $product->merek ? trim($product->merek->name) : '';
                $productName = trim($product->name);

                // Deduplicate overlapping parts
                $parts = array_filter([$merekName, $productName]);
                $finalParts = [];
                foreach ($parts as $p1) {
                    $isSubPart = false;
                    foreach ($parts as $p2) {
                        if ($p1 !== $p2 && stripos($p2, $p1) !== false && strlen($p2) > strlen($p1)) {
                            $isSubPart = true;
                            break;
                        }
                    }
                    if (!$isSubPart) {
                        $finalParts[] = $p1;
                    }
                }
                $displayName = implode(' ', array_unique($finalParts));

                // Netto info separately
                $nettoDisplay = '';
                if ($variant && $variant->netto) {
                    $nettoDisplay = trim($variant->netto->netto_value . ($variant->netto->satuan ? ' ' . $variant->netto->satuan : ''));
                }

                // Selling price: ONLY from variant->price, no fallback
                $sellingPrice = ($variant && $variant->price > 0) ? (int)$variant->price : 0;

                // Get first photo
                $photo = $product->photos->first();

                $variantGroups[$groupKey] = [
                    'id'            => $groupKey,
                    'product_id'    => $product->id,
                    'variant_id'    => $variant ? $variant->id : null,
                    'is_bundle'     => $product->is_bundle,
                    'name'          => $displayName,
                    'netto'         => $nettoDisplay,
                    'offline_price' => $sellingPrice,
                    'photo'         => $photo ? asset($photo->foto) : null,
                    'batches'       => [],
                    'total_stock'   => 0,
                ];
            }

            $variantGroups[$groupKey]['batches'][] = [
                'id'            => $batch->id,
                'batch_no'      => $batch->batch_no,
                'qty'           => $batch->qty,
                'selling_price' => $variantGroups[$groupKey]['offline_price'],
            ];
            $variantGroups[$groupKey]['total_stock'] += $batch->qty;
        }

        // --- BUNDLING LOGIC ---
        $bundleQuery = Product::where('is_bundle', true)
            ->where('status', 'Y')
            ->with(['merek', 'photos', 'bundleItems.product.batches', 'variants']);
        if ($request->search) {
            $bundleQuery->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('merek', fn($mq) => $mq->where('name', 'like', '%' . $request->search . '%'));
            });
        }
        if ($request->merek_id) {
            $bundleQuery->where('merek_id', $request->merek_id);
        }

        $bundles = $bundleQuery->get();
        foreach ($bundles as $bundle) {
            $groupKey = 'b_' . $bundle->id;
            
            // Calculate virtual stock
            $minStock = -1;
            foreach ($bundle->bundleItems as $bi) {
                $componentStock = $bi->product->batches->where('warehouse_id', $warehouseId)->sum('qty');
                $possibleBundles = floor($componentStock / $bi->quantity);
                if ($minStock == -1 || $possibleBundles < $minStock) {
                    $minStock = $possibleBundles;
                }
            }
            if ($minStock == -1) $minStock = 0;

            $merekName   = $bundle->merek ? trim($bundle->merek->name) : '';
            $productName = trim($bundle->name);
            $displayName = trim($merekName . ' ' . $productName); // Simplified for bundles

            $photo = $bundle->photos->first();

            $variantGroups[$groupKey] = [
                'id'            => $groupKey,
                'product_id'    => $bundle->id,
                'variant_id'    => null,
                'is_bundle'     => true,
                'name'          => "[BUNDLE] " . $displayName,
                'netto'         => '',
                'offline_price' => (int)($bundle->price ?: ($bundle->variants->first() ? $bundle->variants->first()->price : 0)),
                'photo'         => $photo ? asset($photo->foto) : null,
                'batches'       => [],
                'total_stock'   => (int)$minStock,
            ];
        }
        // --- END BUNDLING LOGIC ---

        // Filter out entries with no stock or no price, then re-index
        $result = array_values(array_filter($variantGroups, function($v) {
            return $v['total_stock'] > 0 && $v['offline_price'] > 0;
        }));

        return response()->json($result);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:product_batches,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.discount' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|in:cash,qris,transfer,debit',
            'payment_status' => 'required|in:draft,unpaid,paid,canceled',
            'notes' => 'nullable|string',
            'discount_manual' => 'nullable|numeric|min:0',
            'voucher_code' => 'nullable|string|exists:vouchers,code',
            'generate_invoice' => 'nullable|boolean',
            'created_at' => 'nullable|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'cash_received' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            return DB::transaction(function() use ($request) {
                $mainWarehouse = \App\Models\Warehouse::where('type', 'main')->first();
                $warehouseId = $mainWarehouse ? $mainWarehouse->id : 1;

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

            $stockService = new StockService();

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = $item['qty'];
                $itemDiscount = (float)($item['discount'] ?? 0);

                if ($product->is_bundle) {
                    $basePrice = (int)$product->price;
                    $batchId = null;
                } else {
                    $batch = ProductBatch::with(['product', 'variant'])->findOrFail($item['batch_id']);
                    if ($batch->qty < $qty) {
                        throw new \Exception("Stok batch {$batch->batch_no} untuk produk {$product->name} tidak mencukupi.");
                    }
                    if (!$batch->variant || $batch->variant->price <= 0) {
                        throw new \Exception("Produk {$product->name} tidak memiliki harga jual pada variannya.");
                    }
                    $basePrice = (int)$batch->variant->price;
                    $batchId = $batch->id;
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

                $subtotal = ($finalPrice * $qty) - $itemDiscount;
                $totalAmount += $subtotal;
                
                if ($affiliate) {
                    $affiliateFeeTotal += ($itemFee * $qty);
                }

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_batch_id' => $batchId,
                    'buy_price' => $product->is_bundle ? 0 : ($batch->buy_price ?? 0),
                    'qty' => $qty,
                    'price' => $finalPrice, 
                    'discount' => $itemDiscount,
                    'subtotal' => $subtotal,
                    'is_bundle' => $product->is_bundle // Temporary marker
                ];

                // Manual decrement is handled later for single items, 
                // but for bundles we will use StockService which handles it internally.
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
                        }
                    }
                }

                $finalTotal = $totalAmount - $finalDiscount;

                // Validation: Cash Received
                if ($request->payment_method === 'cash') {
                    $cashReceived = (float)($request->cash_received ?? 0);
                    if ($cashReceived < $finalTotal) {
                        throw new \Exception("Uang yang diterima (" . number_format($cashReceived, 0, ',', '.') . ") kurang dari total belanja (" . number_format($finalTotal, 0, ',', '.') . ").");
                    }
                }

                $transaction = Transaction::create([
                    'user_id' => auth()->id(),
                    'customer_id' => $request->customer_id,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
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
                    'created_at' => $request->created_at ? \Carbon\Carbon::parse($request->created_at)->setTimeFrom(now()) : now(),
                    'warehouse_id' => $warehouseId,
                ]);

                if ($request->generate_invoice) {
                    $transaction->update([
                        'invoice_number' => InvoiceService::generate()
                    ]);
                }

                foreach ($itemsToCreate as $itemData) {
                    $isBundle = $itemData['is_bundle'] ?? false;
                    unset($itemData['is_bundle']);

                    $itemData['transaction_id'] = $transaction->id;
                    $mainItem = TransactionItem::create($itemData);

                    if ($request->payment_status === 'paid') {
                        if ($isBundle) {
                            $stockService->explodeBundleComponents($itemData['product_id'], $itemData['qty'], $transaction->id, $mainItem->id);
                        } else {
                            $batch = ProductBatch::find($itemData['product_batch_id']);
                            if ($batch) {
                                $batch->decrement('qty', $itemData['qty']);
                            }
                            $product = Product::find($itemData['product_id']);
                            if ($product) {
                                $product->decrement('stock', $itemData['qty']);
                            }
                        }
                    }
                }

                return response()->json([
                    'success' => true, 
                    'message' => 'Transaksi berhasil disimpan',
                    'transaction_id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number
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

        $now = now();
        if (($voucher->start_date && $now->lt($voucher->start_date)) || 
            ($voucher->end_date && $now->gt($voucher->end_date))) {
            return response()->json(['success' => false, 'message' => 'Voucher tidak dapat digunakan saat ini'], 400);
        }

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

    public function searchInvitation(Request $request)
    {
        $phone = $request->get('phone', '');
        if (strlen($phone) < 3) {
            return response()->json(['status' => 'error', 'data' => []]);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->get('https://invitation.apotekparahyangansuite.com/api-search.php', ['phone' => $phone]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'data' => [], 'message' => $e->getMessage()]);
        }
    }
}
