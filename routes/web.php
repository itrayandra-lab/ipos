<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestController;

# Admin Controllers
use App\Http\Controllers\Admin\DashboardController as DashboardAdmin;
use App\Http\Controllers\Admin\ManageMaster\UserController as UserAdmin;
use App\Http\Controllers\Admin\ManageMaster\CategoryController as CategoryAdmin;
use App\Http\Controllers\Admin\ManageMaster\ProductController as ProductAdmin;
use App\Http\Controllers\Admin\ManageMaster\VoucherController as VoucherAdmin;
use App\Http\Controllers\Admin\ManageMaster\AttributeGroupController as AttributeGroupAdmin;
use App\Http\Controllers\Admin\ManageMaster\AttributeController as AttributeAdmin;
use App\Http\Controllers\Admin\TransactionController as TransactionAdmin;
use App\Http\Controllers\Admin\BatchController as BatchAdmin;
use App\Http\Controllers\Admin\OnlineSaleController as OnlineSaleAdmin;
use App\Http\Controllers\Admin\ChannelSettingController as ChannelSettingAdmin;
use App\Http\Controllers\Admin\StoreSettingController as StoreSettingAdmin;

use App\Http\Controllers\Admin\PosController as PosAdmin;
use App\Http\Controllers\Admin\AffiliateController as AffiliateAdmin;
use App\Http\Controllers\Admin\CustomerController as CustomerAdmin;

# Sales Controllers
use App\Http\Controllers\Sales\DashboardController as DashboardSales;
use App\Http\Controllers\Sales\ManageMaster\CategoryController as CategorySales;
use App\Http\Controllers\Sales\ManageMaster\ProductController as ProductSales;
use App\Http\Controllers\Sales\TransactionController as TransactionSales;

/*
|--------------------------------------------------------------------------
| Web Routes develop by kuli it tecno
|--------------------------------------------------------------------------
*/

# -------------------- AUTH --------------------
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

# -------------------- Guest --------------------
Route::get('/', [GuestController::class, 'home'])->name('home');
Route::get('/cart', [GuestController::class, 'showCart'])->name('cart.show');
Route::post('/cart/fetch', [GuestController::class, 'fetchCart'])->name('cart.fetch');
Route::get('/product/{slug}', [GuestController::class, 'showProduct'])->name('product.show');
Route::post('/checkout', [GuestController::class, 'checkout'])->name('checkout');
Route::post('/midtrans/callback', [GuestController::class, 'callback'])->name('midtrans.callback');
Route::get('/checkout-success', [GuestController::class, 'success'])->name('checkout.success');
Route::post('/voucher', [GuestController::class, 'voucher'])->name('checkout.voucher');

# -------------------- ADMIN --------------------
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    # Dashboard
    Route::get('/', [DashboardAdmin::class, 'index']);

    # Manage Data Member
    Route::prefix('manage-master')->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', [UserAdmin::class, 'index']);
            Route::post('/', [UserAdmin::class, 'create']);
            Route::get('all', [UserAdmin::class, 'getall']);
            Route::post('get', [UserAdmin::class, 'get']);
            Route::post('update', [UserAdmin::class, 'update']);
            Route::delete('/', [UserAdmin::class, 'delete']);
        });
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryAdmin::class, 'index']);
            Route::post('/', [CategoryAdmin::class, 'create']);
            Route::get('all', [CategoryAdmin::class, 'getall']);
            Route::post('get', [CategoryAdmin::class, 'get']);
            Route::post('update', [CategoryAdmin::class, 'update']);
            Route::delete('/', [CategoryAdmin::class, 'delete']);
        });
        Route::prefix('voucher')->group(function () {
            Route::get('/', [VoucherAdmin::class, 'index']);
            Route::get('/create', [VoucherAdmin::class, 'create_view']);
            Route::post('/', [VoucherAdmin::class, 'create']);
            Route::get('all', [VoucherAdmin::class, 'getall']);
            Route::post('get', [VoucherAdmin::class, 'get']);
            Route::post('update', [VoucherAdmin::class, 'update']);
            Route::delete('/', [VoucherAdmin::class, 'delete']);
        });
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductAdmin::class, 'index']);
            Route::post('/', [ProductAdmin::class, 'create']);
            Route::get('/all', [ProductAdmin::class, 'getall']);
        Route::get('/get-pricing/{id}', [ProductAdmin::class, 'getPricing']);
        Route::post('/sync-price', [ProductAdmin::class, 'syncPrice'])->name('admin.products.sync-price');
        Route::post('/get', [ProductAdmin::class, 'get']);
            Route::post('update', [ProductAdmin::class, 'update']);
            Route::delete('/', [ProductAdmin::class, 'delete']);
        });
        Route::prefix('batches')->group(function () {
            Route::get('/{product_id}', [BatchAdmin::class, 'index'])->name('admin.batches.index');
            Route::post('/', [BatchAdmin::class, 'store'])->name('admin.batches.store');
            Route::post('update', [BatchAdmin::class, 'update'])->name('admin.batches.update');
            Route::delete('/', [BatchAdmin::class, 'delete'])->name('admin.batches.delete');
        });

        Route::prefix('attribute-groups')->group(function () {
            Route::get('/', [AttributeGroupAdmin::class, 'index'])->name('admin.manage_master.attribute_groups.index');
            Route::get('/all', [AttributeGroupAdmin::class, 'getall'])->name('admin.manage_master.attribute_groups.all');
            Route::post('/', [AttributeGroupAdmin::class, 'store'])->name('admin.manage_master.attribute_groups.store');
            Route::post('/get', [AttributeGroupAdmin::class, 'get'])->name('admin.manage_master.attribute_groups.get');
            Route::post('/update', [AttributeGroupAdmin::class, 'update'])->name('admin.manage_master.attribute_groups.update');
            Route::delete('/', [AttributeGroupAdmin::class, 'delete'])->name('admin.manage_master.attribute_groups.delete');
        });

        Route::prefix('attributes')->group(function () {
            Route::get('/', [AttributeAdmin::class, 'index'])->name('admin.manage_master.attributes.index');
            Route::get('/all', [AttributeAdmin::class, 'getall'])->name('admin.manage_master.attributes.all');
            Route::post('/', [AttributeAdmin::class, 'store'])->name('admin.manage_master.attributes.store');
            Route::post('/get', [AttributeAdmin::class, 'get'])->name('admin.manage_master.attributes.get');
            Route::post('/update', [AttributeAdmin::class, 'update'])->name('admin.manage_master.attributes.update');
            Route::delete('/', [AttributeAdmin::class, 'delete'])->name('admin.manage_master.attributes.delete');
        });
    });

    Route::prefix('online-sale')->group(function () {
        Route::get('/', [OnlineSaleAdmin::class, 'index'])->name('admin.online_sale.index');
        Route::post('/', [OnlineSaleAdmin::class, 'store'])->name('admin.online_sale.store');
        Route::get('/history', [OnlineSaleAdmin::class, 'history'])->name('admin.online_sale.history');
        Route::get('/edit/{id}', [OnlineSaleAdmin::class, 'edit'])->name('admin.online_sale.edit');
        Route::post('/update/{id}', [OnlineSaleAdmin::class, 'update'])->name('admin.online_sale.update');
        Route::delete('/{id}', [OnlineSaleAdmin::class, 'destroy'])->name('admin.online_sale.destroy');
    });

    Route::prefix('settings')->group(function () {
        Route::get('/store', [StoreSettingAdmin::class, 'index'])->name('admin.settings.store');
        Route::post('/store', [StoreSettingAdmin::class, 'update'])->name('admin.settings.store.update');
        Route::get('/channels', [ChannelSettingAdmin::class, 'index'])->name('admin.settings.channels');
        Route::post('/channels/store', [ChannelSettingAdmin::class, 'store'])->name('admin.settings.channels.store');
        Route::post('/channels/update', [ChannelSettingAdmin::class, 'update'])->name('admin.settings.channels.update');
        Route::post('/channels/delete', [ChannelSettingAdmin::class, 'delete'])->name('admin.settings.channels.delete');
    });

        Route::prefix('affiliate-users')->group(function () {
            Route::get('/', [AffiliateAdmin::class, 'index'])->name('admin.affiliates.index');
            Route::get('/all', [AffiliateAdmin::class, 'getall'])->name('admin.affiliates.all');
            Route::post('/', [AffiliateAdmin::class, 'store'])->name('admin.affiliates.store');
            Route::post('/get', [AffiliateAdmin::class, 'get'])->name('admin.affiliates.get');
            Route::post('/update', [AffiliateAdmin::class, 'update'])->name('admin.affiliates.update');
            Route::delete('/', [AffiliateAdmin::class, 'delete'])->name('admin.affiliates.delete');
            
            // Product Specific Commissions
            Route::get('/commissions/{id}', [AffiliateAdmin::class, 'commissions'])->name('admin.affiliates.commissions');
            Route::get('/commissions/{id}/data', [AffiliateAdmin::class, 'commissionsData'])->name('admin.affiliates.commissions.data');
            Route::post('/commissions/store', [AffiliateAdmin::class, 'storeCommission'])->name('admin.affiliates.commissions.store');
            Route::delete('/commissions/delete', [AffiliateAdmin::class, 'deleteCommission'])->name('admin.affiliates.commissions.delete');
            Route::get('/rates/{id}', [AffiliateAdmin::class, 'getRates'])->name('admin.affiliates.rates');
            Route::get('/show/{id}', [AffiliateAdmin::class, 'show'])->name('admin.affiliates.show');
        });

    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerAdmin::class, 'index'])->name('admin.customers.index');
        Route::get('/all', [CustomerAdmin::class, 'getall'])->name('admin.customers.all');
        Route::get('/show/{phone}', [CustomerAdmin::class, 'show'])->name('admin.customers.show');
    });

    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionAdmin::class, 'index']);
        Route::get('all', [TransactionAdmin::class, 'getall']);
        Route::get('print', [TransactionAdmin::class, 'print']);
        Route::get('show/{id}', [TransactionAdmin::class, 'show']);
    });

    Route::prefix('pos')->group(function () {
        Route::get('/', [PosAdmin::class, 'index'])->name('admin.pos.index');
        Route::get('/products', [PosAdmin::class, 'fetchProducts'])->name('admin.pos.products');
        Route::post('/verify-voucher', [PosAdmin::class, 'verifyVoucher'])->name('admin.pos.verify-voucher');
        Route::post('/', [PosAdmin::class, 'store'])->name('admin.pos.store');
        Route::get('/receipt/{id}', [PosAdmin::class, 'printReceipt'])->name('admin.pos.receipt');
    });
});

# -------------------- SALES --------------------
Route::prefix('sales')->middleware(['auth', 'role:sales'])->group(function () {
    # Dashboard
    Route::get('/', [DashboardSales::class, 'index']);

    # Manage Data Member
    Route::prefix('manage-master')->group(function () {
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategorySales::class, 'index']);
            Route::post('/', [CategorySales::class, 'create']);
            Route::get('all', [CategorySales::class, 'getall']);
            Route::post('get', [CategorySales::class, 'get']);
            Route::post('update', [CategorySales::class, 'update']);
            Route::delete('/', [CategorySales::class, 'delete']);
        });
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductSales::class, 'index']);
            Route::post('/', [ProductSales::class, 'create']);
            Route::get('all', [ProductSales::class, 'getall']);
            Route::post('get', [ProductSales::class, 'get']);
            Route::post('update', [ProductSales::class, 'update']);
            Route::delete('/', [ProductSales::class, 'delete']);
        });
    });

    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionSales::class, 'index']);
        Route::get('all', [TransactionSales::class, 'getall']);
        Route::get('print', [TransactionSales::class, 'print']);
        Route::get('show/{id}', [TransactionSales::class, 'show']);
    });

    Route::prefix('pos')->group(function () {
        Route::get('/', [PosAdmin::class, 'index'])->name('sales.pos.index');
        Route::get('/products', [PosAdmin::class, 'fetchProducts'])->name('sales.pos.products');
        Route::post('/verify-voucher', [PosAdmin::class, 'verifyVoucher'])->name('sales.pos.verify-voucher');
        Route::post('/', [PosAdmin::class, 'store'])->name('sales.pos.store');
        Route::get('/receipt/{id}', [PosAdmin::class, 'printReceipt'])->name('sales.pos.receipt');
    });
});
