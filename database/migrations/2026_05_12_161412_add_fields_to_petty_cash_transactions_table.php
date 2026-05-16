<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_category_id')->nullable()->after('user_id');
            $table->date('transaction_date')->nullable()->after('expense_category_id');
            $table->string('receipt_photo')->nullable()->after('transaction_date');

            $table->foreign('expense_category_id')->references('id')->on('expense_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            $table->dropForeign(['expense_category_id']);
            $table->dropColumn(['expense_category_id', 'transaction_date', 'receipt_photo']);
        });
    }
};
