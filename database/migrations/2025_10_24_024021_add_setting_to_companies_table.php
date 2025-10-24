<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('auto_code_products')->default(false)->after('address');
            $table->boolean('auto_code_departments')->default(false)->after('auto_code_products');
            $table->string('product_code_prefix')->nullable()->after('auto_code_departments');
            $table->string('department_code_prefix')->nullable()->after('product_code_prefix');
            $table->string('logo_path')->nullable()->after('department_code_prefix');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'auto_code_products',
                'auto_code_departments',
                'product_code_prefix',
                'department_code_prefix',
                'logo_path',
            ]);
        });
    }
};
