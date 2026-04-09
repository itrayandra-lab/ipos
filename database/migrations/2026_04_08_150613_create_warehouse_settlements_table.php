<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_no')->unique();
            $table->foreignId('warehouse_id')->constrained();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['draft', 'pending', 'verified', 'paid', 'rejected'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_settlements');
    }
};
