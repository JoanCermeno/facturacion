<?php
// database/migrations/2025_09_17_000004_create_product_prices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('price_type_id')->constrained()->onDelete('cascade');
            $table->decimal('price_usd', 12, 2);
            $table->decimal('profit_percentage', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};