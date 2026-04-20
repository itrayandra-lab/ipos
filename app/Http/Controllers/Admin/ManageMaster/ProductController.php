<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\PhotoProduct;
use App\Models\Voucher;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index()
    {
        $categories = Category::select('id', 'name', 'code')->orderBy('name', 'ASC')->get();
        $productTypes = \App\Models\ProductType::select('id', 'name')->orderBy('name', 'ASC')->get();
        $merek = \App\Models\Merek::orderBy('name', 'ASC')->get();
        $netto_attributes = \App\Models\Attribute::whereHas('group', function ($q) {
            $q->where('code', 'NETTO');
        })->orderBy('name', 'ASC')->get();

        $productTiers = \App\Models\ProductTier::all();

        return view('admin.manage_master.products.index')->with([
            'sb' => 'Product',
            'categories' => $categories,
            'productTypes' => $productTypes,
            'merek' => $merek,
            'netto_attributes' => $netto_attributes,
            'productTiers' => $productTiers
        ]);
    }

    public function search(Request $request)
    {
        $warehouseId = $request->warehouse_id;
        $search = $request->search;

        $query = Product::with(['merek', 'variants'])
            ->where('status', 'Y');

        if ($warehouseId) {
            $query->whereHas('batches', function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('merek', function($mq) use ($search) {
                      $mq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->limit(20)->get();

        return response()->json($products);
    }

    public function getall(Request $request)
    {
        $query = Product::with(['merek', 'photos', 'category', 'subCategory', 'productType', 'productTier', 'variants'])
            ->orderBy('id', 'desc')
            ->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('hierarchy', function (Product $product) {
            $hierarchy = [];
            if ($product->category)
                $hierarchy[] = $product->category->name;
            if ($product->subCategory)
                $hierarchy[] = $product->subCategory->name;
            if ($product->productType)
                $hierarchy[] = $product->productType->name;

            return !empty($hierarchy) ? implode(' > ', $hierarchy) : '<span class="text-muted">No Hierarchy</span>';
        })
            ->addColumn('merek_name', function (Product $product) {
            return $product->merek ? $product->merek->name : '-';
        })
            ->addColumn('variant_count', function (Product $product) {
            return $product->variants->count() . ' SKUs';
        })
            ->addColumn('status', function (Product $product) {
            return $product->status == 'Y' ? 'Aktif' : 'Non Aktif';
        })
            ->addColumn('photos_preview', function (Product $product) {
            $firstPhoto = $product->photos->first();
            if ($firstPhoto) {
                return '<img src="' . asset($firstPhoto->foto) . '" width="50" class="img-thumbnail">';
            }
            return '<img src="' . asset('assets/img/Asset 3.png') . '" width="50" class="img-thumbnail">';
        })
            ->addColumn('action', function (Product $product) {
            return '
                <div class="dropdown d-inline dropleft">
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true" data-toggle="dropdown">
                        Action
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="' . route('admin.products.show', $product->id) . '" class="dropdown-item">Detail</a></li>
                        <li><a data-id="' . $product->id . '" class="dropdown-item edit">Edit</a></li>
                        <li><a data-id="' . $product->id . '" class="dropdown-item hapus" href="#">Hapus</a></li>
                    </ul>
                </div>
                ';
        })
            ->rawColumns(['hierarchy', 'photos_preview', 'action'])
            ->make(true);
    }


    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'merek_id' => 'required|exists:merek,id',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'product_type_id' => 'required|exists:product_types,id',
            'product_tier_id' => 'nullable|exists:product_tiers,id',
            'min_stock_alert' => 'required|integer|min:0',
            'variants' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Check for product exists (optional based on requirements, here we follow existing logic)
            
            $merek = \App\Models\Merek::find($request->merek_id);
            $category = \App\Models\Category::find($request->category_id);
            $merekCode = $merek?->code ?? 'UNK';
            $categoryCode = $category?->code ?? 'UNK';
            $productCodeInput = strtoupper(trim($request->code ?? 'UNK'));
            // Remove any spaces from product code for SKU consistency
            $productCode = preg_replace('/\s+/', '', $productCodeInput);

            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            $product = Product::create([
                'name' => $request->name,
                'code' => $productCodeInput,
                'slug' => $slug,
                'merek_id' => $request->merek_id,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'product_type_id' => $request->product_type_id,
                'product_tier_id' => $request->product_tier_id,
                'stock' => 0,
                'min_stock_alert' => $request->min_stock_alert,
                'status' => $request->status,
            ]);

            // Save Variants
            foreach ($request->variants as $v) {
                $netto = \App\Models\ProductNetto::firstOrCreate([
                    'product_id' => $product->id,
                    'netto_value' => $v['netto']
                ], [
                    'satuan' => $v['satuan'] ?? null
                ]);

                // Update satuan if it already exists
                if (isset($v['satuan'])) {
                    $netto->update(['satuan' => $v['satuan']]);
                }

                // Auto-generate variant_name from Product Name + Netto + Satuan
                $variantName = $product->name . ' ' . $v['netto'] . ($v['satuan'] ?? '');

                // Auto-generate SKU
                $nettoPart = preg_replace('/[^0-9]/', '', $v['netto']);
                $generatedSku = strtoupper("{$merekCode}-{$categoryCode}-{$productCode}-{$nettoPart}");
                
                // Ensure SKU is unique by adding suffix if needed (Case-Insensitive Check)
                $finalSku = $generatedSku;
                $counter = 1;
                while (\App\Models\ProductVariant::whereRaw('LOWER(sku_code) = ?', [strtolower($finalSku)])->exists()) {
                    $finalSku = $generatedSku . '-' . $counter++;
                }

                \App\Models\ProductVariant::create([
                    'product_netto_id' => $netto->id,
                    'sku_code'         => $finalSku,
                    'variant_name'     => $variantName,
                    'price'            => $v['price'] ?? 0,
                    'price_real'       => (!empty($v['price_real']) && $v['price_real'] > 0) ? $v['price_real'] : 0,
                    'price_tier'       => (!empty($v['price_tier']) && $v['price_tier'] > 0) ? $v['price_tier'] : 0,
                    'stock'            => 0,
                ]);
            }

            if ($request->hasFile('foto')) {
                foreach ($request->file('foto') as $file) {
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $path = 'assets/product/' . $filename;
                    $file->move(public_path('assets/product'), $filename);
                    PhotoProduct::create([
                        'foto' => $path,
                        'id_product' => $product->id,
                    ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Data produk berhasil disimpan']);

        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            
            // Specifically handle duplicate entry for SKU_CODE if the loop somehow failed
            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi konflik data (Duplicate SKU). Mohon periksa kembali kode produk atau netto yang dimasukkan.'
                ], 422);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data karena kesalahan database: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }


    public function get(Request $request)
    {
        $product = Product::with(['category', 'subCategory', 'productType', 'productTier', 'variants.netto', 'photos'])->findOrFail($request->id);
        return response()->json($product, 200);
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'product_type_id' => 'required|exists:product_types,id',
            'product_tier_id' => 'nullable|exists:product_tiers,id',
            'min_stock_alert' => 'required|integer|min:0',
            'variants' => 'required|array|min:1',
            'deleted_photos' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $slug = Str::slug($request->name);
            $count = Product::where('slug', $slug)->where('id', '!=', $id)->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }

            $productCodeInput = strtoupper(trim($request->code ?? 'UNK'));
            $productCode = preg_replace('/\s+/', '', $productCodeInput);

            $product->update([
                'name' => $request->name,
                'code' => $productCodeInput,
                'slug' => $slug,
                'merek_id' => $request->merek_id,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'product_type_id' => $request->product_type_id,
                'product_tier_id' => $request->product_tier_id,
                'min_stock_alert' => $request->min_stock_alert,
                'status' => $request->status,
            ]);

            // Validate SKU uniqueness (excluding current product's existing SKUs)
            $merek = \App\Models\Merek::find($request->merek_id);
            $category = \App\Models\Category::find($request->category_id);
            $merekCode = $merek?->code ?? 'UNK';
            $categoryCode = $category?->code ?? 'UNK';
            
            // Simple sync for variants: delete all and recreate or update
            $existingVariantIds = [];
            foreach ($request->variants as $v) {
                $netto = \App\Models\ProductNetto::firstOrCreate([
                    'product_id' => $product->id,
                    'netto_value' => $v['netto']
                ], [
                    'satuan' => $v['satuan'] ?? null
                ]);

                // Update satuan if it already exists
                if (isset($v['satuan'])) {
                    $netto->update(['satuan' => $v['satuan']]);
                }

                // Auto-generate variant_name from Product Name + Netto + Satuan
                $variantName = $product->name . ' ' . $v['netto'] . ($v['satuan'] ?? '');

                // Auto-generate SKU
                $nettoPart = preg_replace('/[^0-9]/', '', $v['netto']);
                $generatedSku = strtoupper("{$merekCode}-{$categoryCode}-{$productCode}-{$nettoPart}");

                // Check if variant already exists for this netto
                $existingVariant = \App\Models\ProductVariant::where('product_netto_id', $netto->id)->first();

                if ($existingVariant) {
                    // Update existing variant — keep existing SKU to avoid conflicts
                    $existingVariant->update([
                        'variant_name' => $variantName,
                        'price'        => $v['price'] ?? 0,
                        'price_real'   => (!empty($v['price_real']) && $v['price_real'] > 0) ? $v['price_real'] : 0,
                        'price_tier'   => (!empty($v['price_tier']) && $v['price_tier'] > 0) ? $v['price_tier'] : 0,
                    ]);
                    $variant = $existingVariant;
                } else {
                    // Create new variant — ensure SKU is unique by adding suffix if needed (Case-Insensitive Check)
                    $finalSku = $generatedSku;
                    $counter = 1;
                    while (\App\Models\ProductVariant::whereRaw('LOWER(sku_code) = ?', [strtolower($finalSku)])->exists()) {
                        $finalSku = $generatedSku . '-' . $counter++;
                    }
                    $variant = \App\Models\ProductVariant::create([
                        'product_netto_id' => $netto->id,
                        'sku_code'         => $finalSku,
                        'variant_name'     => $variantName,
                        'price'            => $v['price'] ?? 0,
                        'price_real'       => (!empty($v['price_real']) && $v['price_real'] > 0) ? $v['price_real'] : 0,
                        'price_tier'       => (!empty($v['price_tier']) && $v['price_tier'] > 0) ? $v['price_tier'] : 0,
                        'stock'            => 0,
                    ]);
                }
                $existingVariantIds[] = $variant->id;
            }

            // Sync variants: delete those not in the request
            \App\Models\ProductVariant::whereIn('product_netto_id', function ($query) use ($product) {
                $query->select('id')->from('product_nettos')->where('product_id', $product->id);
            })->whereNotIn('id', $existingVariantIds)->delete();

            // Cleanup empty product_nettos
            \App\Models\ProductNetto::where('product_id', $product->id)->doesntHave('variants')->delete();

            if ($request->has('deleted_photos') && !empty($request->deleted_photos)) {
                $deletedPhotoIds = explode(',', $request->deleted_photos);
                foreach ($deletedPhotoIds as $photoId) {
                    $photo = PhotoProduct::find($photoId);
                    if ($photo && $photo->id_product == $id) {
                        if (file_exists(public_path($photo->foto))) {
                            unlink(public_path($photo->foto));
                        }
                        $photo->delete();
                    }
                }
            }

            if ($request->hasFile('foto')) {
                foreach ($request->file('foto') as $file) {
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $path = 'assets/product/' . $filename;
                    $file->move(public_path('assets/product'), $filename);
                    PhotoProduct::create([
                        'foto' => $path,
                        'id_product' => $id,
                    ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Product updated successfully']);

        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi konflik data (Duplicate SKU). Mohon periksa kembali kode produk atau netto yang dimasukkan.'
                ], 422);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui varian produk: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }


    public function delete(Request $request)
    {
        $product = Product::findOrFail($request->id);

        Voucher::where('product_id', $request->id)->update(['status' => 'NON ACTIVE']);

        foreach ($product->photos as $photo) {
            if (file_exists(public_path($photo->foto))) {
                unlink(public_path($photo->foto));
            }
            $photo->delete();
        }

        // Clean up variants and nettos
        foreach ($product->nettos as $netto) {
            $netto->variants()->delete();
            $netto->delete();
        }

        $product->delete();
        return response()->json(['status' => 'success', 'message' => 'Data produk berhasil dihapus'], 200);
    }

    public function getPricing($id)
    {
        $product = Product::with('batches')->findOrFail($id);
        $channels = \App\Models\ChannelSetting::all();

        $recommendations = [];

        foreach ($channels as $channel) {
            $recommendations[$channel->slug] = \App\Services\PricingService::calculateForProduct($product, $channel->slug);
        }

        $batches = $product->batches->map(function ($batch) use ($channels) {
            $prices = [];

            foreach ($channels as $channel) {
                $prices[$channel->slug] = \App\Services\PricingService::calculate($batch, $channel->slug);
            }

            return [
            'batch_no' => $batch->batch_no,
            'expiry_date' => $batch->expiry_date->format('d M Y'),
            'buy_price' => $batch->buy_price,
            'prices' => $prices
            ];
        });

        return response()->json([
            'product' => $product,
            'batches' => $batches,
            'recommendations' => $recommendations,
            'channels' => $channels
        ]);
    }

    public function syncPrice(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->update([
            'price' => $request->price,
            'price_real' => $request->price
        ]);

        return response()->json(['message' => 'Harga resmi produk berhasil diperbarui']);
    }

    public function show($id)
    {
        $product = Product::with(['merek', 'category', 'subCategory', 'productType', 'variants.netto', 'photos', 'batches'])->findOrFail($id);
        
        return view('admin.manage_master.products.show', compact('product'))->with([
            'sb' => 'Product'
        ]);
    }
}