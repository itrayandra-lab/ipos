<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_stock_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique();
            $table->foreignId('branch_warehouse_id')->constrained('warehouses');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('shipped_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'pending',    // baru diajukan
                'approved',   // disetujui pusat
                'rejected',   // ditolak pusat
                'shipped',    // sedang dikirim
                'received',   // diterima cabang
                'cancelled',  // dibatalkan
            ])->default('pending');
            $table->text('notes')->nullable();                 // catatan pengajuan dari cabang
            $table->text('approval_notes')->nullable();        // catatan saat approve/reject
            $table->text('shipping_notes')->nullable();        // catatan pengiriman
            $table->text('receipt_notes')->nullable();         // catatan penerimaan dari cabang
            $table->string('receipt_photo')->nullable();       // foto bukti terima
            $table->text('rejection_reason')->nullable();      // alasan penolakan
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_stock_requests');
    }
};
