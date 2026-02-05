<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = ['category_id', 'name', 'slug', 'price','price_real', 'stock', 'neto', 'pieces', 'status', 'min_stock_alert'];

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function photos()
    {
        return $this->hasMany(PhotoProduct::class, 'id_product');
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
