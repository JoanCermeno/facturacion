<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_operations', function (Blueprint $table) {
            $table->id();
            $table->enum('operation_type', ['cargo', 'descargo', 'ajuste']);
            $table->unsignedBigInteger('operation_number'); // correlativo por tipo
            $table->date('operation_date');
            $table->string('note')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // usuario que hace la operación
            $table->string('responsible'); // persona responsable
            $table->foreignId('company_id')->constrained()->onDelete('cascade'); // empresa
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_operations');
    }
};
