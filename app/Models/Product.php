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
        'product_type_id',
        'name',
        'slug',
        'price',
        'status',
        'price_real',
        'stock',
        'min_stock_alert',
        'neto',
        'pieces'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
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

    /**
     * Recalculate and sync the stock column.
     * Formula: SUM(batches.incoming_qty) - SUM(transaction_items.qty)
     */
    public function syncStock()
    {
        $incoming = $this->batches()->sum('qty');
        $outgoing = $this->transactionItems()->sum('qty');

        $this->update(['stock' => $incoming - $outgoing]);
    }
}
