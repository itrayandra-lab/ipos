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
        Schema::create('fund_requests', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('request_code')->unique();
            $blueprint->foreignId('user_id')->constrained('users'); // Requester
            $blueprint->string('title');
            $blueprint->text('description');
            $blueprint->decimal('amount', 15, 2);
            
            // Status: pending, manager_approved, manager_rejected, finance_approved, finance_rejected, disbursed
            $blueprint->string('status')->default('pending');
            
            // Approvals
            $blueprint->foreignId('manager_id')->nullable()->constrained('users');
            $blueprint->timestamp('manager_approved_at')->nullable();
            $blueprint->text('manager_notes')->nullable();
            
            $blueprint->foreignId('finance_id')->nullable()->constrained('users');
            $blueprint->timestamp('finance_approved_at')->nullable();
            $blueprint->text('finance_notes')->nullable();
            
            $blueprint->string('attachment')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_requests');
    }
};
