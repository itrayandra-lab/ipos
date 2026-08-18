<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierDeliveryNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_delivery_note_id',
        'product_batch_id',
        'qty',
        'notes',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(SupplierDeliveryNote::class, 'supplier_delivery_note_id');
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }
}
