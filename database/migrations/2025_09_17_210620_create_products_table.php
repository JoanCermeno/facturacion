<?php
// database/migrations/2025_09_17_000002_create_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companies_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('restrict');
            $table->string('code');
            $table->unique(['companies_id', 'code']); // Unicidad por empresa
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->enum('base_unit', [
                'unit', 'box', 'pack', 'pair', 'dozen',
                'kg', 'gr', 'lb', 'oz',
                'lt', 'ml', 'gal',
                'm', 'cm', 'mm', 'inch',
                'sqm', 'sqft',
                'hour', 'day',
                'service'
            ])->default('unit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
