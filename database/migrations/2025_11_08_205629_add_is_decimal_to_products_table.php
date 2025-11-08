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
        // Usamos Schema::table() para seleccionar la tabla existente 'products'
        Schema::table('products', function (Blueprint $table) {
            // Agregamos el nuevo campo 'is_decimal'
            // El campo boolean (o booleano) solo puede ser TRUE (1) o FALSE (0).
            // Lo definimos como false por defecto, asumiendo que la mayoría de productos
            // se venden por unidades (enteros).
            $table->boolean('is_decimal')->default(false)->after('base_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Usamos Schema::table() para seleccionar la tabla existente 'products'
        Schema::table('products', function (Blueprint $table) {
            // Revertir: Eliminar el campo si se hace un rollback
            $table->dropColumn('is_decimal');
        });
    }
};