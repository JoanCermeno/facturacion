<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_operation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_id')->constrained('inventory_operations')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity'); // cantidad afectada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_operation_details');
    }
};
