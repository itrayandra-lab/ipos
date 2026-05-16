<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'warehouse_id'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function hasPermission($slug): bool
    {
        // Super Admin has all permissions
        if ($this->isSuperAdmin()) return true;
        
        return $this->permissions()->where('slug', $slug)->exists();
    }

    /** 
     * Memeriksa apakah user bisa melakukan aksi tulis (Create/Edit/Delete)
     * Untuk role Finance, dibatasi hanya View Only kecuali di grup 'Finance'
     */
    public function canEdit($slug): bool
    {
        if ($this->isSuperAdmin()) return true;
        
        // Cek apakah user punya akses ke menu tersebut dulu
        if (!$this->hasPermission($slug)) return false;

        // Aturan khusus Finance: View Only untuk selain grup 'Finance'
        if ($this->isFinance()) {
            $permission = \App\Models\Permission::where('slug', $slug)->first();
            if ($permission && $permission->group !== 'Finance') {
                return false;
            }
        }

        return true;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'user_warehouses');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isStoreManager(): bool
    {
        return $this->role === 'store_manager';
    }

    public function isFinance(): bool
    {
        return $this->role === 'finance';
    }

    public function isOperations(): bool
    {
        return $this->role === 'admin';
    }

    public function isSales(): bool
    {
        return $this->role === 'sales';
    }

    public function isBranch(): bool
    {
        return $this->role === 'branch';
    }

    /** Semua role yang bisa akses area admin */
    public function isAdminGroup(): bool
    {
        return in_array($this->role, ['super_admin', 'store_manager', 'finance', 'admin', 'sales', 'branch']);
    }
}
