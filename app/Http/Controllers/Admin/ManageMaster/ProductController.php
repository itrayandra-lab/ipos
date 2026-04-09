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

        // Validate SKU uniqueness
        // Since SKU is now automatic, we calculate it here for validation
        $merek = \App\Models\Merek::find($request->merek_id);
        $category = \App\Models\Category::find($request->category_id);
        $merekCode = $merek?->code ?? 'UNK';
        $categoryCode = $category?->code ?? 'UNK';
        $productCode = $request->code ?? 'UNK';

        foreach ($request->variants as $index => $v) {
            $nettoPart = preg_replace('/[^0-9]/', '', $v['netto']);
            $generatedSku = strtoupper("{$merekCode}-{$categoryCode}-{$productCode}-{$nettoPart}");
            
            $existingSku = \App\Models\ProductVariant::where('sku_code', $generatedSku)->first();
            if ($existingSku) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Auto-generated SKU "' . $generatedSku . '" sudah digunakan. Silakan periksa kembali kode produk atau netto.'
                ], 422);
            }
            // Store the generated SKU back to the request array if needed, but we'll use it during save
        }

        $slug = Str::slug($request->name);
        $count = Product::where('slug', $slug)->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $product = Product::create([
            'name' => $request->name,
            'code' => $request->code,
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

            try {
                \App\Models\ProductVariant::create([
                    'product_netto_id' => $netto->id,
                    'sku_code' => $generatedSku,
                    'variant_name' => $variantName,
                    'price' => $v['price'],
                    'price_real' => $v['price_real'] ?? $v['price'],
                    'price_tier' => $v['price_tier'] ?? null,
                ]);
            }
            catch (\Illuminate\Database\QueryException $e) {
                // Delete the product if variant creation fails
                $product->delete();

                // Check if it's a duplicate SKU error
                if (strpos($e->getMessage(), 'Duplicate entry') !== false || $e->getCode() == 23000) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Kode SKU "' . $v['sku'] . '" sudah digunakan oleh produk lain. Silakan gunakan kode SKU yang berbeda.'
                    ], 422);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat menyimpan varian produk. Silakan coba lagi.'
                ], 500);
            }
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

        return response()->json(['status' => 'success', 'message' => 'Data produk berhasil disimpan']);
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

        $slug = Str::slug($request->name);
        $count = Product::where('slug', $slug)->where('id', '!=', $id)->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $product->update([
            'name' => $request->name,
            'code' => $request->code,
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
        $productCode = $request->code ?? 'UNK';

        $currentSkus = \App\Models\ProductVariant::whereHas('netto', function ($q) use ($product) {
            $q->where('product_id', $product->id);
        })->pluck('sku_code')->toArray();

        foreach ($request->variants as $v) {
            $nettoPart = preg_replace('/[^0-9]/', '', $v['netto']);
            $generatedSku = strtoupper("{$merekCode}-{$categoryCode}-{$productCode}-{$nettoPart}");

            // Skip if this SKU belongs to the current product (it's being edited)
            if (!in_array($generatedSku, $currentSkus)) {
                $existingSku = \App\Models\ProductVariant::where('sku_code', $generatedSku)->first();
                if ($existingSku) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Auto-generated SKU "' . $generatedSku . '" sudah digunakan oleh produk lain.'
                    ], 422);
                }
            }
        }

        // Simple sync for variants: delete all and recreate or update
        // For simplicity in this "simpler flow", we'll update or create
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

            try {
                $variant = \App\Models\ProductVariant::updateOrCreate(
                ['product_netto_id' => $netto->id, 'sku_code' => $generatedSku],
                [
                    'variant_name' => $variantName,
                    'price' => $v['price'],
                    'price_real' => $v['price_real'] ?? $v['price'],
                    'price_tier' => $v['price_tier'] ?? null,
                ]
                );
                $existingVariantIds[] = $variant->id;
            }
            catch (\Illuminate\Database\QueryException $e) {
                // Check if it's a duplicate SKU error
                if (strpos($e->getMessage(), 'Duplicate entry') !== false || $e->getCode() == 23000) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Kode SKU "' . $v['sku'] . '" sudah digunakan oleh produk lain. Silakan gunakan kode SKU yang berbeda.'
                    ], 422);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat memperbarui varian produk. Silakan coba lagi.'
                ], 500);
            }
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

        return response()->json(['status' => 'success', 'message' => 'Product updated successfully']);
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