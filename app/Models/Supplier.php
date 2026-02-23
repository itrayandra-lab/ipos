<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'postal_code',
        'bank_name',
        'account_number',
        'account_holder_name',
        'npwp',
        'tax_status',
        'payment_terms',
        'notes',
        'status',
        'created_by',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class , 'created_by');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    // Auto-generate supplier code
    public static function generateCode()
    {
        $lastSupplier = self::orderBy('code', 'desc')->first();

        if ($lastSupplier) {
            $lastNumber = intval(substr($lastSupplier->code, 4)); // SUP-XXXXX
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        }
        else {
            $newNumber = '00001';
        }

        return 'SUP-' . $newNumber;
    }
}
