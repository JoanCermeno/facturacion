<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companies_id')
                ->constrained('companies')
                ->cascadeOnDelete(); // 🔗 Cada método pertenece a una empresa
            $table->string('code');
            $table->string('description');
            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->cascadeOnDelete();
            $table->enum('status', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            // 💡 Evitamos duplicados dentro de una misma empresa
            $table->unique(['companies_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
