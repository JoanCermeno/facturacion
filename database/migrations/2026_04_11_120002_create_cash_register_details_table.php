<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_register_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cash_register_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->decimal('initial_amount', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('cash_register_id')->references('id')->on('cash_registers')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_register_details');
    }
};
