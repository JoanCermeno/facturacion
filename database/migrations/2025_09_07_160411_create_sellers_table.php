<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ci', 12)->comment('número de cédula del vendedor');
            $table->decimal('commission_percentage', 5, 2)->default(0)->comment('porcentaje de comisión');
            $table->foreignId('fk_company')->constrained('companys')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
