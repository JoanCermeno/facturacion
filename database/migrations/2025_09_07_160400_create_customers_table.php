<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
              $table->foreignId('companies_id')
          ->constrained('companies')
          ->cascadeOnUpdate()
          ->cascadeOnDelete();
            $table->string('id_card')->nullable(); // Cédula o identificación
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('address')->nullable(); // Dirección
            $table->string('phone')->nullable();
            $table->timestamps();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
