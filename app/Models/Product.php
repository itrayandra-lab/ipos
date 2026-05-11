<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'merek_id',
        'category_id',
        'sub_category_id',
        'product_type_id',
        'name',
        'code',
        'slug',
        'price',
        'status',
        'price_real',
        'stock',
        'min_stock_alert',
        'neto',
        'pieces',
        'product_tier_id',
        'supplier_id',
        'is_bundle'
    ];

    public function bundleItems()
    {
        return $this->hasMany(BundleItem::class, 'bundle_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function productTier()
    {
        return $this->belongsTo(ProductTier::class);
    }

    public function nettos()
    {
        return $this->hasMany(ProductNetto::class);
    }

    public function variants()
    {
        return $this->hasManyThrough(ProductVariant::class , ProductNetto::class , 'product_id', 'product_netto_id');
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function merek()
    {
        return $this->belongsTo(Merek::class , 'merek_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function photos()
    {
        return $this->hasMany(PhotoProduct::class , 'id_product');
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function supplierReturnItems()
    {
        return $this->hasMany(SupplierReturnItem::class);
    }

    /**
     * Recalculate and sync the stock column.
     * Formula: SUM(batches.incoming_qty) - SUM(transaction_items.qty)
     */
    public function syncStock()
    {
        self::bulkSyncStock([$this->id]);
    }

    public static function bulkSyncStock($productIds)
    {
        if (empty($productIds)) return;
        
        $ids = implode(',', array_map('intval', $productIds));
        
        \DB::statement("
            UPDATE products 
            SET stock = (SELECT COALESCE(SUM(qty), 0) FROM product_batches WHERE product_id = products.id) 
                       - (SELECT COALESCE(SUM(qty), 0) FROM transaction_items WHERE product_id = products.id) 
                       - (SELECT COALESCE(SUM(qty), 0) FROM supplier_return_items WHERE product_id = products.id)
            WHERE id IN ($ids)
        ");
    }

    /**
     * Get the active selling price for this product.
     * Logic: If bundle, sum components based on approval status.
     * If not bundle, delegate to first variant or fallback.
     */
    public function getSellingPrice()
    {
        if ($this->is_bundle) {
            $items = $this->bundleItems()->with('product.variants')->get();
            if ($items->isEmpty()) return $this->price;

            $allApproved = true;
            $totalHet = 0;
            $totalLegacy = 0;

            foreach ($items as $item) {
                $comp = $item->product;
                if (!$comp) continue;

                $variant = $comp->variants->first();
                if (!$variant) {
                    $allApproved = false;
                    $totalLegacy += ($comp->price * $item->quantity);
                    $totalHet += ($comp->price_real > 0 ? $comp->price_real : $comp->price) * $item->quantity;
                    continue;
                }

                if (!$variant->is_approved) {
                    $allApproved = false;
                }

                $totalHet += ($variant->het_online > 0 ? $variant->het_online : $variant->price) * $item->quantity;
                $totalLegacy += ($variant->price > 0 ? $variant->price : $variant->het_online) * $item->quantity;
            }

            return $allApproved ? $totalHet : $totalLegacy;
        }

        // For non-bundle products
        $variant = $this->variants->first();
        if ($variant) {
            return $variant->getSellingPrice();
        }

        return $this->price_real > 0 ? $this->price_real : $this->price;
    }

    /**
     * Sync the 'price' field for bundles based on transition logic.
     */
    public function syncBundlePrice()
    {
        if (!$this->is_bundle) return;
        
        $price = $this->getSellingPrice();
        $this->update(['price' => $price]);
        
        // Also update the first variant price if exists
        $variant = $this->variants->first();
        if ($variant) {
            $variant->update(['price' => $price]);
        }
    }
}
