<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Índice 1: Ventas por estado y fecha ──────────────────────────────
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'sales_status_created_at_index');
        });

        // ── Índice 2: Sale items por producto ────────────────────────────────
        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('product_id', 'sale_items_product_id_index');
        });

        // ── Índice 3: Productos por empresa y stock ──────────────────────────
        Schema::table('products', function (Blueprint $table) {
            $table->index(['companies_id', 'stock'], 'products_companies_id_stock_index');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['companies_id', 'stock']);
        });
    }
};