<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_returns', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique();
            $table->foreignId('branch_warehouse_id')->constrained('warehouses');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'pending',   // baru diajukan
                'approved',  // disetujui pusat
                'rejected',  // ditolak pusat
                'shipped',   // sudah dikirim dari cabang ke pusat
                'received',  // diterima pusat
                'cancelled', // dibatalkan
            ])->default('pending');
            $table->text('reason')->nullable();           // alasan return dari cabang
            $table->text('approval_notes')->nullable();   // catatan dari pusat
            $table->text('receipt_notes')->nullable();    // catatan penerimaan oleh pusat
            $table->string('receipt_photo')->nullable();  // foto bukti kirim/terima
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_returns');
    }
};
