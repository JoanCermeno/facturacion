<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('cash_register_id')->nullable()->after('id');
            $table->decimal('total', 12, 2)->default(0)->after('commission_percentage');
            $table->enum('status', ['completed', 'voided'])->default('completed')->after('total');

            $table->foreign('cash_register_id')->references('id')->on('cash_registers')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['cash_register_id']);
            $table->dropColumn(['cash_register_id', 'total', 'status']);
        });
    }
};
