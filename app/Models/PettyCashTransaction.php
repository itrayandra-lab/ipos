<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'amount', 'description', 
        'reference_id', 'balance_after', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
