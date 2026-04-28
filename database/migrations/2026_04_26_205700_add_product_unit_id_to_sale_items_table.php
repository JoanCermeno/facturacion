<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_unit_id')->nullable()->after('product_id');
            $table->foreign('product_unit_id')->references('id')->on('product_units')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->dropColumn('product_unit_id');
        });
    }
};
