<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_sales', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique();
            $table->foreignId('branch_warehouse_id')->constrained('warehouses');
            $table->foreignId('user_id')->constrained('users');
            $table->date('sale_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_sales');
    }
};
