<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $row) {
            $row->id();
            $row->string('name');
            $row->string('slug')->unique();
            $row->string('group')->nullable(); // Dashboard, Produk, Stok, dll
            $row->timestamps();
        });

        Schema::create('permission_user', function (Blueprint $row) {
            $row->id();
            $row->foreignId('user_id')->constrained()->onDelete('cascade');
            $row->foreignId('permission_id')->constrained()->onDelete('cascade');
            $row->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permissions');
    }
};
