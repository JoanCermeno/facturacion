<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin', 'cashier']);
            $table->foreignId('fk_user')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
