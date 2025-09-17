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
            $table->string('symbol', 5)->unique(); // Ej: USD, VES, COP
            $table->string('name', 50);             // Ej: Dólar, Bolívar, Peso Colombiano
            $table->decimal('exchange_rate', 15, 6); // Tasa (cuánto vale 1 USD en esa moneda)
            $table->boolean('is_base')->default(false); // Solo una moneda base (USD)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
