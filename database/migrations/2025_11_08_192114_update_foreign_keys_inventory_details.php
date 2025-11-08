<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('inventory_operation_details', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('restrict'); // o no action
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_operation_details', function (Blueprint $table) {
            // Quitar la foreign key actual
            $table->dropForeign(['product_id']);

            // Restaurar la foreign key anterior (probablemente cascade)
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('cascade');
        });
    }
};
