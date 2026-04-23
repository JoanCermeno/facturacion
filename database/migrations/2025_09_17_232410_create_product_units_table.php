<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('unit_type', 50);
            $table->decimal('conversion_factor', 12, 4)->default(1);
            // Ejemplo: 1 caja = 12 unidades, 1 galón = 3.785 litros
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};