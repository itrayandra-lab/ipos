<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'address', 'type', 'status'];

    public function productBatches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function stockMovementsFrom()
    {
        return $this->hasMany(StockMovement::class, 'from_warehouse_id');
    }

    public function stockMovementsTo()
    {
        return $this->hasMany(StockMovement::class, 'to_warehouse_id');
    }

    public function settlements()
    {
        return $this->hasMany(WarehouseSettlement::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function branchStockRequests()
    {
        return $this->hasMany(BranchStockRequest::class, 'branch_warehouse_id');
    }

    public function branchSales()
    {
        return $this->hasMany(BranchSale::class, 'branch_warehouse_id');
    }

    public function branchReturns()
    {
        return $this->hasMany(BranchReturn::class, 'branch_warehouse_id');
    }

    public function isBranch(): bool
    {
        return $this->type === 'branch';
    }

    public function isMain(): bool
    {
        return $this->type === 'main';
    }
}
