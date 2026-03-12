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
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->text('delivery_address')->nullable()->after('customer_phone');
        });

        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropColumn('delivery_address');
        });

        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
