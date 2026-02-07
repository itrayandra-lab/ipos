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
        $categories = Category::select('id', 'name')->orderBy('name', 'ASC')->get();
        $netto_attributes = \App\Models\Attribute::whereHas('group', function($q) {
            $q->where('code', 'NETTO');
        })->orderBy('name', 'ASC')->get();
        
        return view('admin.manage_master.products.index')->with([
            'sb' => 'Product',
            'categories' => $categories,
            'netto_attributes' => $netto_attributes
        ]);
    }

    public function getall(Request $request)
    {
        $query = Product::with(['category', 'photos'])
            ->leftJoin('vouchers', function($join) {
                $join->on('products.id', '=', 'vouchers.product_id')
                     ->where('vouchers.status', '=', 'ACTIVE');
            })
            ->addSelect('products.*', 'vouchers.percent as discount_percent', 'vouchers.name as voucher_name')
            ->orderBy('id', 'desc')
            ->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('category_name', function (Product $product) {
                return $product->category ? $product->category->name : '<span class="text-muted">No Category</span>';
            })
            ->addColumn('status', function (Product $product) {
                return $product->status == 'Y' ?  'Aktif' : 'Non Aktif';
            })
            ->addColumn('photos_preview', function (Product $product) {
                $firstPhoto = $product->photos->first();
                if ($firstPhoto) {
                    return '<img src="' . asset($firstPhoto->foto) . '" width="50" class="img-thumbnail">';
                }
                return '<img src="' . asset('assets/img/Asset 3.png') . '" width="50" class="img-thumbnail">';
            })
            ->addColumn('price_display', function (Product $product) {
                $currentPrice = $product->price;
                $realPrice = $product->price_real ?? $currentPrice;
                
                if ($realPrice > $currentPrice && isset($product->discount_percent) && $product->discount_percent > 0) {
                    $discountInfo = $product->discount_percent . '% off dari ' . $product->voucher_name;
                    return '
                        <span class="text-muted"><del>Rp ' . number_format($realPrice) . '</del></span><br>
                        <strong class="text-success">Rp ' . number_format($currentPrice) . '</strong>
                        <small class="text-danger d-block">' . $discountInfo . '</small>
                    ';
                } elseif ($realPrice > $currentPrice) {
                    $discountPercent = round((($realPrice - $currentPrice) / $realPrice) * 100);
                    return '
                        <span class="text-muted"><del>Rp ' . number_format($realPrice) . '</del></span><br>
                        <strong class="text-success">Rp ' . number_format($currentPrice) . '</strong>
                        <small class="text-danger d-block">(' . $discountPercent . '% off)</small>
                    ';
                } else {
                    return '<strong>Rp ' . number_format($currentPrice) . '</strong>';
                }
            })
            ->addColumn('action', function (Product $product) {
                return '
                <div class="dropdown d-inline dropleft">
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true" data-toggle="dropdown">
                        Action
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="' . route('admin.batches.index', $product->id) . '" class="dropdown-item">Batches (Stock)</a></li>
                        <li><a href="#" data-id="' . $product->id . '" class="dropdown-item view-pricing">Rincian Harga</a></li>
                        <li><a data-id="' . $product->id . '" class="dropdown-item edit">Edit</a></li>
                        <li><a data-id="' . $product->id . '" class="dropdown-item hapus" href="#">Hapus</a></li>
                    </ul>
                </div>
                ';
            })
            ->rawColumns(['category_name', 'photos_preview', 'price_display', 'action'])
            ->make(true);
    }


    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'raw_price' => 'required|numeric|min:0',
            'min_stock_alert' => 'required|integer|min:0',
            'neto' => 'nullable|string',
            'pieces' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $slug = Str::slug($request->name);
        $count = Product::where('slug', $slug)->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $product = Product::create([
            'name' => $request->name,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'price' => $request->raw_price,
            'stock' => 0, // Initial stock is 0 until batches are added
            'min_stock_alert' => $request->min_stock_alert,
            'neto' => $request->neto,
            'pieces' => $request->pieces,
            'status' => $request->status,
        ]);

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

        return redirect()->back()->with('message', 'Data produk berhasil disimpan');
    }

    public function get(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->photos = $product->photos()->get(['id', 'foto']);
        return response()->json($product, 200);
    }

    public function update(Request $request)
    {
        $id = $request->id;
        if (!$id) {
            return redirect()->back()->with('error', 'ID produk tidak ditemukan');
        }
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'raw_price' => 'required|numeric|min:0',
            'min_stock_alert' => 'required|integer|min:0',
            'neto' => 'nullable|string',
            'pieces' => 'nullable|string',
            'deleted_photos' => 'nullable|string', 
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Cek voucher aktif untuk produk ini
        $voucher = Voucher::where('product_id', $id)->where('status', 'ACTIVE')->first();
        $newPrice = (float) $request->raw_price;
        $hasDiscount = false;
        $resetDiscount = false;

        if ($voucher) {
            $expectedDiscountedPrice = round($product->price_real * (1 - ($voucher->percent / 100)), 2);
            if ($newPrice > $expectedDiscountedPrice || $newPrice < $expectedDiscountedPrice) {
                $resetDiscount = true;
                $voucher->update(['status' => 'NON ACTIVE']);
                $message = 'Data produk berhasil diupdate dan diskon voucher dinonaktifkan karena harga tidak sesuai dengan diskon.';
            } else {
                $hasDiscount = true;
                $message = 'Data produk berhasil diupdate.';
            }
        } else {
            $message = 'Data produk berhasil diupdate.';
        }

        $slug = Str::slug($request->name);
        $count = Product::where('slug', $slug)
                    ->where('id', '!=', $id)
                    ->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $updateData = [
            'name' => $request->name,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'min_stock_alert' => $request->min_stock_alert,
            'neto' => $request->neto,
            'pieces' => $request->pieces,
            'status' => $request->status,
        ];

        if ($hasDiscount) {
        $updateData['price'] = $newPrice;
    } else {
        $updateData['price'] = $newPrice;
        $updateData['price_real'] = $newPrice;
    }

        $product->update($updateData);

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

        return redirect()->back()->with('message', $message);
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
        
        $product->delete();
        return response()->json(['message' => 'Data produk berhasil dihapus'], 200);
    }

    public function getPricing($id)
    {
        $product = Product::with('batches')->findOrFail($id);
        $channels = \App\Models\ChannelSetting::all();
        
        $recommendations = [];

        foreach ($channels as $channel) {
            $recommendations[$channel->slug] = \App\Services\PricingService::calculateForProduct($product, $channel->slug);
        }

        $batches = $product->batches->map(function($batch) use ($channels) {
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
}