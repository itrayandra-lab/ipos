<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestController;

# Admin Controllers
use App\Http\Controllers\Admin\DashboardController as DashboardAdmin;
use App\Http\Controllers\Admin\ManageMaster\UserController as UserAdmin;
use App\Http\Controllers\Admin\ManageMaster\MerekController as MerekAdmin;
use App\Http\Controllers\Admin\ManageMaster\CategoryController as CategoryAdmin;
use App\Http\Controllers\Admin\ManageMaster\SubCategoryController as SubCategoryAdmin;
use App\Http\Controllers\Admin\ManageMaster\ProductTypeController as ProductTypeAdmin;
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
use App\Http\Controllers\Admin\SalesDocumentController as SalesDocumentAdmin;
use App\Http\Controllers\Admin\BankAccountController as BankAccountAdmin;

# Sales Controllers
use App\Http\Controllers\Sales\DashboardController as DashboardSales;
use App\Http\Controllers\Sales\ManageMaster\MerekController as MerekSales;
use App\Http\Controllers\Sales\ManageMaster\ProductController as ProductSales;
use App\Http\Controllers\Sales\TransactionController as TransactionSales;

/* |-------------------------------------------------------------------------- | Web Routes develop by kuli it tecno |-------------------------------------------------------------------------- */

# -------------------- AUTH --------------------
Route::get('/login', [AuthController::class , 'index'])->name('login');
Route::post('/login', [AuthController::class , 'login']);
Route::get('/logout', [AuthController::class , 'logout'])->name('logout');

# -------------------- Guest --------------------
Route::get('/', [GuestController::class , 'home'])->name('home');
Route::get('/cart', [GuestController::class , 'showCart'])->name('cart.show');
Route::post('/cart/fetch', [GuestController::class , 'fetchCart'])->name('cart.fetch');
Route::get('/product/{slug}', [GuestController::class , 'showProduct'])->name('product.show');
Route::post('/checkout', [GuestController::class , 'checkout'])->name('checkout');
Route::post('/midtrans/callback', [GuestController::class , 'callback'])->name('midtrans.callback');
Route::get('/checkout-success', [GuestController::class , 'success'])->name('checkout.success');
Route::post('/voucher', [GuestController::class , 'voucher'])->name('checkout.voucher');

# Share Links (Public - No Auth Required)


# -------------------- ADMIN --------------------
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    # Dashboard
    Route::get('/', [DashboardAdmin::class , 'index']);

    # Manage Data Member
    Route::prefix('manage-master')->group(function () {
            Route::prefix('users')->group(function () {
                    Route::get('/', [UserAdmin::class , 'index']);
                    Route::post('/', [UserAdmin::class , 'create']);
                    Route::get('all', [UserAdmin::class , 'getall']);
                    Route::post('get', [UserAdmin::class , 'get']);
                    Route::post('update', [UserAdmin::class , 'update']);
                    Route::delete('/', [UserAdmin::class , 'delete']);
                }
                );
                Route::prefix('merek')->group(function () {
                    Route::get('/', [MerekAdmin::class , 'index']);
                    Route::post('/', [MerekAdmin::class , 'create']);
                    Route::get('all', [MerekAdmin::class , 'getall']);
                    Route::post('get', [MerekAdmin::class , 'get']);
                    Route::post('update', [MerekAdmin::class , 'update']);
                    Route::delete('/', [MerekAdmin::class , 'delete']);
                }
                );
                Route::prefix('categories')->group(function () {
                    Route::get('/', [CategoryAdmin::class , 'index']);
                    Route::post('/', [CategoryAdmin::class , 'create']);
                    Route::get('all', [CategoryAdmin::class , 'getall']);
                    Route::post('get', [CategoryAdmin::class , 'get']);
                    Route::post('update', [CategoryAdmin::class , 'update']);
                    Route::delete('/', [CategoryAdmin::class , 'delete']);
                    Route::get('get-subs', [CategoryAdmin::class , 'getSubCategories'])->name('admin.categories.get-subs');
                }
                );
                Route::prefix('sub-categories')->group(function () {
                    Route::get('/', [SubCategoryAdmin::class , 'index']);
                    Route::post('/', [SubCategoryAdmin::class , 'create']);
                    Route::get('all', [SubCategoryAdmin::class , 'getall']);
                    Route::post('get', [SubCategoryAdmin::class , 'get']);
                    Route::post('update', [SubCategoryAdmin::class , 'update']);
                    Route::delete('/', [SubCategoryAdmin::class , 'delete']);
                }
                );
                Route::prefix('product-types')->group(function () {
                    Route::get('/', [ProductTypeAdmin::class , 'index']);
                    Route::post('/', [ProductTypeAdmin::class , 'create']);
                    Route::get('all', [ProductTypeAdmin::class , 'getall']);
                    Route::post('get', [ProductTypeAdmin::class , 'get']);
                    Route::post('update', [ProductTypeAdmin::class , 'update']);
                    Route::delete('/', [ProductTypeAdmin::class , 'delete']);
                }
                );
                Route::prefix('voucher')->group(function () {
                    Route::get('/', [VoucherAdmin::class , 'index']);
                    Route::get('/create', [VoucherAdmin::class , 'create_view']);
                    Route::post('/', [VoucherAdmin::class , 'create']);
                    Route::get('all', [VoucherAdmin::class , 'getall']);
                    Route::post('get', [VoucherAdmin::class , 'get']);
                    Route::post('update', [VoucherAdmin::class , 'update']);
                    Route::delete('/', [VoucherAdmin::class , 'delete']);
                }
                );
                Route::prefix('products')->group(function () {
                    Route::get('/', [ProductAdmin::class , 'index'])->name('admin.products.index');
                    Route::post('/', [ProductAdmin::class , 'create']);
                    Route::get('/all', [ProductAdmin::class , 'getall'])->name('admin.products.all');
                    Route::get('/search', [ProductAdmin::class , 'search'])->name('admin.products.search');
                    Route::get('/get-pricing/{id}', [ProductAdmin::class , 'getPricing']);
                    Route::post('/sync-price', [ProductAdmin::class , 'syncPrice'])->name('admin.products.sync-price');
                    Route::post('/get', [ProductAdmin::class , 'get']);
                    Route::post('update', [ProductAdmin::class , 'update']);
                    Route::delete('/', [ProductAdmin::class , 'delete']);
                    Route::get('/show/{id}', [ProductAdmin::class , 'show'])->name('admin.products.show');
                }
                );
                Route::prefix('batches')->group(function () {
                    Route::get('/{product_id}', [BatchAdmin::class , 'index'])->name('admin.batches.index');
                    Route::post('/', [BatchAdmin::class , 'store'])->name('admin.batches.store');
                    Route::post('update', [BatchAdmin::class , 'update'])->name('admin.batches.update');
                    Route::delete('/', [BatchAdmin::class , 'delete'])->name('admin.batches.delete');
                }
                );
                Route::prefix('stock')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\ManageMaster\StockController::class , 'index']);
                    Route::get('/all', [\App\Http\Controllers\Admin\ManageMaster\StockController::class , 'getall']);
                    Route::post('/', [\App\Http\Controllers\Admin\ManageMaster\StockController::class , 'create']);
                    Route::post('/get', [\App\Http\Controllers\Admin\ManageMaster\StockController::class , 'get']);
                    Route::post('/update', [\App\Http\Controllers\Admin\ManageMaster\StockController::class , 'update']);
                    Route::delete('/', [\App\Http\Controllers\Admin\ManageMaster\StockController::class , 'delete']);
                    Route::get('/variants/{product_id}', [\App\Http\Controllers\Admin\ManageMaster\StockController::class , 'getVariants']);
                    Route::post('/detail', [\App\Http\Controllers\Admin\ManageMaster\StockController::class , 'getDetail']);
                    Route::post('/get-netto', [\App\Http\Controllers\Admin\ManageMaster\StockController::class , 'getNetto']);
                    Route::post('/update-netto', [\App\Http\Controllers\Admin\ManageMaster\StockController::class , 'updateNetto']);
                    Route::post('/add-netto', [\App\Http\Controllers\Admin\ManageMaster\StockController::class , 'addNetto']);
                }
                );

                Route::prefix('attribute-groups')->group(function () {
                    Route::get('/', [AttributeGroupAdmin::class , 'index'])->name('admin.manage_master.attribute_groups.index');
                    Route::get('/all', [AttributeGroupAdmin::class , 'getall'])->name('admin.manage_master.attribute_groups.all');
                    Route::post('/', [AttributeGroupAdmin::class , 'store'])->name('admin.manage_master.attribute_groups.store');
                    Route::post('/get', [AttributeGroupAdmin::class , 'get'])->name('admin.manage_master.attribute_groups.get');
                    Route::post('/update', [AttributeGroupAdmin::class , 'update'])->name('admin.manage_master.attribute_groups.update');
                    Route::delete('/', [AttributeGroupAdmin::class , 'delete'])->name('admin.manage_master.attribute_groups.delete');
                }
                );

                Route::prefix('attributes')->group(function () {
                    Route::get('/', [AttributeAdmin::class , 'index'])->name('admin.manage_master.attributes.index');
                    Route::get('/all', [AttributeAdmin::class , 'getall'])->name('admin.manage_master.attributes.all');
                    Route::post('/', [AttributeAdmin::class , 'store'])->name('admin.manage_master.attributes.store');
                    Route::post('/get', [AttributeAdmin::class , 'get'])->name('admin.manage_master.attributes.get');
                    Route::post('/update', [AttributeAdmin::class , 'update'])->name('admin.manage_master.attributes.update');
                    Route::delete('/', [AttributeAdmin::class , 'delete'])->name('admin.manage_master.attributes.delete');
                }
                );

                Route::prefix('product-tiers')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\ManageMaster\ProductTierController::class , 'index']);
                    Route::post('/', [\App\Http\Controllers\Admin\ManageMaster\ProductTierController::class , 'create']);
                    Route::get('all', [\App\Http\Controllers\Admin\ManageMaster\ProductTierController::class , 'getall']);
                    Route::post('get', [\App\Http\Controllers\Admin\ManageMaster\ProductTierController::class , 'get']);
                    Route::post('update', [\App\Http\Controllers\Admin\ManageMaster\ProductTierController::class , 'update']);
                    Route::delete('/', [\App\Http\Controllers\Admin\ManageMaster\ProductTierController::class , 'delete']);
                }
                );
            }
            );

            Route::prefix('online-sale')->group(function () {
            Route::get('/', [OnlineSaleAdmin::class , 'index'])->name('admin.online_sale.index');
            Route::get('all', [OnlineSaleAdmin::class , 'getall']);
            Route::get('/history', [OnlineSaleAdmin::class , 'index']); // Legacy compatibility
            Route::post('/', [OnlineSaleAdmin::class , 'store'])->name('admin.online_sale.store');
            Route::get('/create', [OnlineSaleAdmin::class , 'create'])->name('admin.online_sale.create');
            Route::get('/edit/{id}', [OnlineSaleAdmin::class , 'edit'])->name('admin.online_sale.edit');
            Route::post('/update/{id}', [OnlineSaleAdmin::class , 'update'])->name('admin.online_sale.update');
            Route::delete('/{id}', [OnlineSaleAdmin::class , 'destroy'])->name('admin.online_sale.destroy');
        }
        );

        Route::prefix('settings')->group(function () {
            Route::get('/store', [StoreSettingAdmin::class , 'index'])->name('admin.settings.store');
            Route::post('/store', [StoreSettingAdmin::class , 'update'])->name('admin.settings.store.update');
            Route::get('/channels', [ChannelSettingAdmin::class , 'index'])->name('admin.settings.channels');
            Route::post('/channels/store', [ChannelSettingAdmin::class , 'store'])->name('admin.settings.channels.store');
            Route::post('/channels/update', [ChannelSettingAdmin::class , 'update'])->name('admin.settings.channels.update');
            Route::post('/channels/delete', [ChannelSettingAdmin::class , 'delete'])->name('admin.settings.channels.delete');
            Route::get('/bank-accounts', [BankAccountAdmin::class , 'index'])->name('admin.settings.bank_accounts');
            Route::get('/bank-accounts/all', [BankAccountAdmin::class , 'getall'])->name('admin.bank_accounts.all');
            Route::post('/bank-accounts/store', [BankAccountAdmin::class , 'store'])->name('admin.bank_accounts.store');
            Route::post('/bank-accounts/get', [BankAccountAdmin::class , 'get'])->name('admin.bank_accounts.get');
            Route::post('/bank-accounts/update', [BankAccountAdmin::class , 'update'])->name('admin.bank_accounts.update');
            Route::delete('/bank-accounts/delete', [BankAccountAdmin::class , 'delete'])->name('admin.bank_accounts.delete');
        }
        );

        Route::prefix('affiliate-users')->group(function () {
            Route::get('/', [AffiliateAdmin::class , 'index'])->name('admin.affiliates.index');
            Route::get('/all', [AffiliateAdmin::class , 'getall'])->name('admin.affiliates.all');
            Route::post('/', [AffiliateAdmin::class , 'store'])->name('admin.affiliates.store');
            Route::post('/get', [AffiliateAdmin::class , 'get'])->name('admin.affiliates.get');
            Route::post('/update', [AffiliateAdmin::class , 'update'])->name('admin.affiliates.update');
            Route::delete('/', [AffiliateAdmin::class , 'delete'])->name('admin.affiliates.delete');

            // Product Specific Commissions
            Route::get('/commissions/{id}', [AffiliateAdmin::class , 'commissions'])->name('admin.affiliates.commissions');
            Route::get('/commissions/{id}/data', [AffiliateAdmin::class , 'commissionsData'])->name('admin.affiliates.commissions.data');
            Route::post('/commissions/store', [AffiliateAdmin::class , 'storeCommission'])->name('admin.affiliates.commissions.store');
            Route::delete('/commissions/delete', [AffiliateAdmin::class , 'deleteCommission'])->name('admin.affiliates.commissions.delete');
            Route::get('/rates/{id}', [AffiliateAdmin::class , 'getRates'])->name('admin.affiliates.rates');
            Route::get('/show/{id}', [AffiliateAdmin::class , 'show'])->name('admin.affiliates.show');
        }
        );

        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomerAdmin::class , 'index'])->name('admin.customers.index');
            Route::post('/', [CustomerAdmin::class , 'store'])->name('admin.customers.store');
            Route::get('/all', [CustomerAdmin::class , 'getall'])->name('admin.customers.all');
            Route::post('/get', [CustomerAdmin::class , 'get'])->name('admin.customers.get');
            Route::post('/update', [CustomerAdmin::class , 'update'])->name('admin.customers.update');
            Route::delete('/', [CustomerAdmin::class , 'delete'])->name('admin.customers.delete');
            Route::get('/check', [CustomerAdmin::class , 'checkAjax'])->name('admin.customers.check');
            Route::post('/store-ajax', [CustomerAdmin::class , 'storeAjax'])->name('admin.customers.store_ajax');
            Route::get('/show/{id}', [CustomerAdmin::class , 'show'])->name('admin.customers.show');
            Route::post('/import', [CustomerAdmin::class , 'import'])->name('admin.customers.import');
            Route::get('/download-template', [CustomerAdmin::class , 'downloadTemplate'])->name('admin.customers.download_template');
        }
        );

        Route::prefix('transactions')->group(function () {
            Route::get('/', [TransactionAdmin::class , 'index'])->name('admin.transactions.index');
            Route::get('all', [TransactionAdmin::class , 'getall']);
            Route::get('print', [TransactionAdmin::class , 'print']);
            Route::get('export/excel', [TransactionAdmin::class , 'exportExcel'])->name('admin.transactions.export.excel');
            Route::get('export/pdf', [TransactionAdmin::class , 'exportPdf'])->name('admin.transactions.export.pdf');
            Route::get('show/{id}', [TransactionAdmin::class , 'show'])->name('admin.transactions.show');
            Route::get('edit/{id}', [TransactionAdmin::class , 'edit'])->name('admin.transactions.edit');
            Route::put('{id}', [TransactionAdmin::class , 'update'])->name('admin.transactions.update');
            Route::delete('{id}', [TransactionAdmin::class , 'destroy'])->name('admin.transactions.destroy');
            Route::get('print-struk/{id}', [TransactionAdmin::class , 'printStruk'])->name('admin.transactions.print_struk');
            Route::post('generate-invoice/{id}', [TransactionAdmin::class , 'generateInvoice'])->name('admin.transactions.generate_invoice');
            Route::post('upload-receipt/{id}', [TransactionAdmin::class , 'uploadReceipt'])->name('admin.transactions.upload-receipt');
            Route::post('settle/{id}', [TransactionAdmin::class , 'settlePayment'])->name('admin.transactions.settle');
            Route::post('update-payment-receipt', [TransactionAdmin::class , 'updatePaymentReceipt'])->name('admin.transactions.update-payment-receipt');
            Route::post('quick-upload-receipt/{id}', [TransactionAdmin::class , 'quickUploadReceipt'])->name('admin.transactions.quick-upload-receipt');



            // Product Sales Report
            Route::get('report/product', [TransactionAdmin::class, 'productReport'])->name('admin.transactions.report.product');
            Route::get('report/product/all', [TransactionAdmin::class, 'productReportData']);
            Route::get('report/product/print', [TransactionAdmin::class, 'printProductReport'])->name('admin.transactions.report.product.print');
        }
        );

        // Finance Module
        Route::prefix('finance')->group(function () {
            // Petty Cash
            Route::get('petty-cash', [App\Http\Controllers\Admin\Finance\PettyCashController::class, 'index'])->name('admin.finance.petty_cash.index');
            Route::get('petty-cash/all', [App\Http\Controllers\Admin\Finance\PettyCashController::class, 'data'])->name('admin.finance.petty_cash.all');
            Route::post('petty-cash', [App\Http\Controllers\Admin\Finance\PettyCashController::class, 'store'])->name('admin.finance.petty_cash.store');

            // Expenses
            Route::get('expenses', [App\Http\Controllers\Admin\Finance\ExpenseController::class, 'index'])->name('admin.finance.expenses.index');
            Route::get('expenses/all', [App\Http\Controllers\Admin\Finance\ExpenseController::class, 'data'])->name('admin.finance.expenses.all');
            Route::post('expenses', [App\Http\Controllers\Admin\Finance\ExpenseController::class, 'store'])->name('admin.finance.expenses.store');
            Route::delete('expenses/{id}', [App\Http\Controllers\Admin\Finance\ExpenseController::class, 'destroy'])->name('admin.finance.expenses.destroy');

            // Expense Categories
            Route::get('expense-categories', [App\Http\Controllers\Admin\Finance\ExpenseCategoryController::class, 'index'])->name('admin.finance.expense_categories.index');
            Route::get('expense-categories/all', [App\Http\Controllers\Admin\Finance\ExpenseCategoryController::class, 'data'])->name('admin.finance.expense_categories.all');
            Route::post('expense-categories', [App\Http\Controllers\Admin\Finance\ExpenseCategoryController::class, 'store'])->name('admin.finance.expense_categories.store');
            Route::post('expense-categories/{id}', [App\Http\Controllers\Admin\Finance\ExpenseCategoryController::class, 'update'])->name('admin.finance.expense_categories.update');
            Route::get('expense-categories/{id}', [App\Http\Controllers\Admin\Finance\ExpenseCategoryController::class, 'show'])->name('admin.finance.expense_categories.show');
            Route::delete('expense-categories/{id}', [App\Http\Controllers\Admin\Finance\ExpenseCategoryController::class, 'destroy'])->name('admin.finance.expense_categories.destroy');

            // Reports
            Route::get('reports', function() { return view('admin.finance.reports.index')->with('sb', 'FinanceReport'); })->name('admin.finance.reports.index');
        });

        // Sales Documents (Penjualan)
        Route::prefix('sales')->group(function () {
            Route::prefix('invoices')->group(function () {
                    Route::get('/', [SalesDocumentAdmin::class , 'invoices'])->name('admin.sales.invoices.index');
                    Route::get('/all', [SalesDocumentAdmin::class , 'getInvoices'])->name('admin.sales.invoices.all');
                    Route::get('/create', [SalesDocumentAdmin::class , 'createInvoice'])->name('admin.sales.invoices.create');
                    Route::post('/', [SalesDocumentAdmin::class , 'storeInvoice'])->name('admin.sales.invoices.store');
                    Route::get('/show/{id}', [SalesDocumentAdmin::class , 'showInvoice'])->name('admin.sales.invoices.show');
                    Route::get('/edit/{id}', [SalesDocumentAdmin::class , 'editInvoice'])->name('admin.sales.invoices.edit');
                    Route::put('/{id}', [SalesDocumentAdmin::class , 'updateInvoice'])->name('admin.sales.invoices.update');
                    Route::post('/upload-receipt/{id}', [SalesDocumentAdmin::class , 'uploadReceipt'])->name('admin.sales.invoices.upload-receipt');
                    Route::post('/settle/{id}', [SalesDocumentAdmin::class , 'settlePayment'])->name('admin.sales.invoices.settle');
                    Route::delete('/{id}', [SalesDocumentAdmin::class , 'destroyInvoice'])->name('admin.sales.invoices.destroy');
                    Route::get('/print/{id}', [SalesDocumentAdmin::class , 'printInvoice'])->name('admin.sales.invoices.print');
                }
                );
                Route::prefix('delivery-notes')->group(function () {
                    Route::get('/', [SalesDocumentAdmin::class , 'deliveryNotes'])->name('admin.sales.delivery_notes.index');
                    Route::get('/all', [SalesDocumentAdmin::class , 'getDeliveryNotes'])->name('admin.sales.delivery_notes.all');
                    Route::get('/create', [SalesDocumentAdmin::class , 'createDeliveryNote'])->name('admin.sales.delivery_notes.create');
                    Route::post('/', [SalesDocumentAdmin::class , 'storeDeliveryNote'])->name('admin.sales.delivery_notes.store');
                    Route::get('/show/{id}', [SalesDocumentAdmin::class , 'showDeliveryNote'])->name('admin.sales.delivery_notes.show');
                    Route::get('/edit/{id}', [SalesDocumentAdmin::class , 'editDeliveryNote'])->name('admin.sales.delivery_notes.edit');
                    Route::put('/{id}', [SalesDocumentAdmin::class , 'updateDeliveryNote'])->name('admin.sales.delivery_notes.update');
                    Route::delete('/{id}', [SalesDocumentAdmin::class , 'destroyDeliveryNote'])->name('admin.sales.delivery_notes.destroy');
                    Route::get('/print/{id}', [SalesDocumentAdmin::class , 'printDeliveryNote'])->name('admin.sales.delivery_notes.print');
                }
                );
                Route::prefix('receipts')->group(function () {
                    Route::get('/', [SalesDocumentAdmin::class , 'receipts'])->name('admin.sales.receipts.index');
                    Route::get('/all', [SalesDocumentAdmin::class , 'getReceipts'])->name('admin.sales.receipts.all');
                    Route::get('/print/{id}', [SalesDocumentAdmin::class , 'printReceipt'])->name('admin.sales.receipts.print');
                }
                );
                Route::prefix('lab-invoices')->group(function () {
                    Route::get('/', [SalesDocumentAdmin::class , 'labInvoices'])->name('admin.sales.lab_invoices.index');
                    Route::get('/all', [SalesDocumentAdmin::class , 'getLabInvoices'])->name('admin.sales.lab_invoices.all');
                    Route::get('/create', [SalesDocumentAdmin::class , 'createLabInvoice'])->name('admin.sales.lab_invoices.create');
                    Route::post('/store', [SalesDocumentAdmin::class , 'storeLabInvoice'])->name('admin.sales.lab_invoices.store');
                    Route::get('/print/{id}', [SalesDocumentAdmin::class , 'printLabInvoice'])->name('admin.sales.lab_invoices.print');
                }
                );
            }
            );

            Route::prefix('pos')->group(function () {
            Route::get('/', [PosAdmin::class , 'index'])->name('admin.pos.index');
            Route::get('/products', [PosAdmin::class , 'fetchProducts'])->name('admin.pos.products');
            Route::post('/verify-voucher', [PosAdmin::class , 'verifyVoucher'])->name('admin.pos.verify-voucher');
            Route::post('/', [PosAdmin::class , 'store'])->name('admin.pos.store');
            Route::get('/receipt/{id}', [PosAdmin::class , 'printReceipt'])->name('admin.pos.receipt');
            Route::get('/search-invitation', [PosAdmin::class , 'searchInvitation'])->name('admin.pos.search_invitation');
        }
        );

        // Purchasing Module
        Route::prefix('purchasing')->group(function () {
            // Suppliers
            Route::controller(\App\Http\Controllers\Admin\Purchasing\SupplierController::class)->group(function () {
                    Route::get('/suppliers', 'index')->name('admin.purchasing.suppliers.index');
                    Route::get('/suppliers/all', 'getall')->name('admin.purchasing.suppliers.getall');
                    Route::post('/suppliers', 'create')->name('admin.purchasing.suppliers.create');
                    Route::post('/suppliers/get', 'get')->name('admin.purchasing.suppliers.get');
                    Route::post('/suppliers/update', 'update')->name('admin.purchasing.suppliers.update');
                    Route::post('/suppliers/delete', 'delete')->name('admin.purchasing.suppliers.delete');
                }
                );

                // Purchase Orders
                Route::controller(\App\Http\Controllers\Admin\Purchasing\PurchaseOrderController::class)->group(function () {
                    Route::get('/purchase-orders', 'index')->name('admin.purchasing.purchase_orders.index');
                    Route::get('/purchase-orders/create', 'create')->name('admin.purchasing.purchase_orders.create');
                    Route::get('/purchase-orders/all', 'getall')->name('admin.purchasing.purchase_orders.getall');
                    Route::get('/purchase-orders/products', 'getProducts')->name('admin.purchasing.purchase_orders.get_products');
                    Route::post('/purchase-orders', 'store')->name('admin.purchasing.purchase_orders.store');
                    Route::post('/purchase-orders/delete', 'delete')->name('admin.purchasing.purchase_orders.delete');
                    Route::get('/purchase-orders/{id}/edit', 'edit')->name('admin.purchasing.purchase_orders.edit');
                    Route::put('/purchase-orders/{id}', 'update')->name('admin.purchasing.purchase_orders.update');
                    Route::get('/purchase-orders/{id}', 'show')->name('admin.purchasing.purchase_orders.show');
                    Route::get('/purchase-orders/{id}/print', 'print')->name('admin.purchasing.purchase_orders.print');
                }
                );

                // Goods Receipts
                Route::controller(\App\Http\Controllers\Admin\Purchasing\GoodsReceiptController::class)->group(function () {
                    Route::get('/goods-receipts', 'index')->name('admin.purchasing.goods_receipts.index');
                    Route::get('/goods-receipts/create', 'create')->name('admin.purchasing.goods_receipts.create');
                    Route::get('/goods-receipts/all', 'getall')->name('admin.purchasing.goods_receipts.getall');
                    Route::get('/goods-receipts/po-items', 'getPoItems')->name('admin.purchasing.goods_receipts.get_po_items');
                    Route::post('/goods-receipts', 'store')->name('admin.purchasing.goods_receipts.store');
                    Route::get('/goods-receipts/{id}', 'show')->name('admin.purchasing.goods_receipts.show');
                }
                );

                // Stock Movements
                Route::controller(\App\Http\Controllers\Admin\Inventory\StockMovementController::class)->group(function () {
                    Route::get('/stock-movements', 'index')->name('admin.stock_movements.index');
                    Route::get('/stock-movements/create', 'create')->name('admin.stock_movements.create');
                    Route::get('/stock-movements/all', 'getall')->name('admin.stock_movements.getall');
                    Route::post('/stock-movements', 'store')->name('admin.stock_movements.store');
                    Route::get('/stock-movements/{id}', 'show')->name('admin.stock_movements.show');
                    Route::post('/stock-movements/{id}/ship', 'ship')->name('admin.stock_movements.ship');
                    Route::post('/stock-movements/{id}/receive', 'receive')->name('admin.stock_movements.receive');
                });

                // Warehouses
                Route::prefix('settings')->group(function () {
                    Route::controller(\App\Http\Controllers\Admin\Inventory\WarehouseController::class)->group(function () {
                        Route::get('/warehouses', 'index')->name('admin.settings.warehouses.index');
                        Route::get('/warehouses/all', 'getall')->name('admin.settings.warehouses.getall');
                        Route::post('/warehouses', 'store')->name('admin.settings.warehouses.store');
                        Route::post('/warehouses/get', 'get')->name('admin.settings.warehouses.get');
                        Route::post('/warehouses/update', 'update')->name('admin.settings.warehouses.update');
                        Route::delete('/warehouses/delete', 'delete')->name('admin.settings.warehouses.delete');
                    });
                });

                // Warehouse Settlements
                Route::controller(\App\Http\Controllers\Admin\Inventory\WarehouseSettlementController::class)->group(function () {
                    Route::get('/settlements', 'index')->name('admin.settlements.index');
                    Route::get('/settlements/all', 'getall')->name('admin.settlements.getall');
                    Route::get('/settlements/create', 'create')->name('admin.settlements.create');
                    Route::post('/settlements', 'store')->name('admin.settlements.store');
                    Route::get('/settlements/{id}', 'show')->name('admin.settlements.show');
                    Route::post('/settlements/{id}/submit', 'submit')->name('admin.settlements.submit');
                    Route::post('/settlements/{id}/verify', 'verify')->name('admin.settlements.verify');
                    Route::post('/settlements/{id}/reject', 'reject')->name('admin.settlements.reject');
                });
            }
            );
        });

# -------------------- SALES --------------------
Route::prefix('sales')->middleware(['auth', 'role:sales'])->group(function () {
    # Dashboard
    Route::get('/', [DashboardSales::class , 'index']);

    # Manage Data Member
    Route::prefix('manage-master')->group(function () {
            Route::prefix('merek')->group(function () {
                    Route::get('/', [MerekSales::class , 'index']);
                    Route::post('/', [MerekSales::class , 'create']);
                    Route::get('all', [MerekSales::class , 'getall']);
                    Route::post('get', [MerekSales::class , 'get']);
                    Route::post('update', [MerekSales::class , 'update']);
                    Route::delete('/', [MerekSales::class , 'delete']);
                }
                );
                Route::prefix('products')->group(function () {
                    Route::get('/', [ProductSales::class , 'index']);
                    Route::post('/', [ProductSales::class , 'create']);
                    Route::get('all', [ProductSales::class , 'getall']);
                    Route::post('get', [ProductSales::class , 'get']);
                    Route::post('update', [ProductSales::class , 'update']);
                    Route::delete('/', [ProductSales::class , 'delete']);
                }
                );
            }
            );

            Route::prefix('transactions')->group(function () {
            Route::get('/', [TransactionSales::class , 'index']);
            Route::get('all', [TransactionSales::class , 'getall']);
            Route::get('print', [TransactionSales::class , 'print']);
            Route::get('show/{id}', [TransactionSales::class , 'show']);
        }
        );

        Route::prefix('pos')->group(function () {
            Route::get('/', [PosAdmin::class , 'index'])->name('sales.pos.index');
            Route::get('/products', [PosAdmin::class , 'fetchProducts'])->name('sales.pos.products');
            Route::post('/verify-voucher', [PosAdmin::class , 'verifyVoucher'])->name('sales.pos.verify-voucher');
            Route::post('/', [PosAdmin::class , 'store'])->name('sales.pos.store');
            Route::get('/receipt/{id}', [PosAdmin::class , 'printReceipt'])->name('sales.pos.receipt');
        }
        );
    });
