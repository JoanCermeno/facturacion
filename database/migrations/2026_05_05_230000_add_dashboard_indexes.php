<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Índices de rendimiento para el Dashboard
 *
 * Los índices compuestos cubren exactamente los patrones de consulta
 * del DashboardController:
 *
 *   1. sales(status, created_at)
 *      → Cubre WHERE status='completed' AND created_at BETWEEN ... (ventas del día/semana)
 *      → Laravel usa este índice para SUM(total), COUNT(*) y GROUP BY DATE(created_at)
 *
 *   2. sale_items(product_id)
 *      → Cubre el JOIN sale_items → products en la query de top-productos
 *      → La FK ya existe, pero el índice explícito garantiza lookup eficiente
 *
 *   3. products(companies_id, stock)
 *      → Cubre WHERE companies_id=? AND stock <= 5 (productos con stock crítico)
 *      → Esencial para el filtro ?stock_lte= que se añade en ProductController
 *
 * NOTA: No se añaden índices en columnas que ya tienen FKs implícitas (sale_id,
 *       company_id) porque MySQL/SQLite los crea automáticamente.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Índice 1: Ventas por estado y fecha ──────────────────────────────
        Schema::table('sales', function (Blueprint $table) {
            // Previene error si el índice ya existe por alguna migración anterior
            if (!$this->hasIndex('sales', 'sales_status_created_at_index')) {
                $table->index(['status', 'created_at'], 'sales_status_created_at_index');
            }
        });

        // ── Índice 2: Sale items por producto ────────────────────────────────
        Schema::table('sale_items', function (Blueprint $table) {
            if (!$this->hasIndex('sale_items', 'sale_items_product_id_index')) {
                $table->index('product_id', 'sale_items_product_id_index');
            }
        });

        // ── Índice 3: Productos por empresa y stock ──────────────────────────
        Schema::table('products', function (Blueprint $table) {
            if (!$this->hasIndex('products', 'products_companies_id_stock_index')) {
                $table->index(['companies_id', 'stock'], 'products_companies_id_stock_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndexIfExists('sales_status_created_at_index');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndexIfExists('sale_items_product_id_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndexIfExists('products_companies_id_stock_index');
        });
    }

    /**
     * Helper: Verifica si un índice ya existe para evitar errores en re-ejecuciones.
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes($table);
        return array_key_exists($indexName, $indexes);
    }
};
