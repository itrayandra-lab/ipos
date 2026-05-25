<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->string('batch_no')->nullable()->after('satuan');
        });
    }

    public function down()
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropColumn('batch_no');
        });
    }
};
