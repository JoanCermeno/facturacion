<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
        $table->id();
        $table->foreignId('companies_id')
            ->constrained('companies')
            ->onDelete('cascade'); // Si eliminas la empresa, se eliminan sus monedas
        $table->string('symbol', 5); // Ej: USD, VES, COP
        $table->string('name', 50);  // Ej: Dólar, Bolívar, Peso Colombiano
        $table->decimal('exchange_rate', 15, 6); // Tasa (cuánto vale 1 USD en esa moneda)
        $table->boolean('is_base')->default(false); // Solo una moneda base por empresa
        $table->enum('conversion_type', ['multiply', 'divide'])->default('multiply');
        $table->timestamps();

        $table->unique(['companies_id', 'symbol']); // Cada empresa no puede repetir el mismo símbolo
    });

    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
