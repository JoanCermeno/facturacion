<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Department;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;

class ProductImportService
{
    public function importProducts(array $productsData, int $companies_id): array
    {
        // Obtener la moneda base de la empresa
        $baseCurrency = Currency::where('companies_id', $companies_id)->where('is_base', true)->first();
        if (!$baseCurrency) {
            throw new \Exception('No se encontró una moneda base para la empresa.');
        }

        // Obtener o crear el departamento por defecto
        $department = Department::where('companies_id', $companies_id)
            ->where(function ($query) {
                $query->where('description', 'Carga Masiva')
                      ->orWhere('description', 'General');
            })
            ->first();

        if (!$department) {
            $department = Department::create([
                'companies_id' => $companies_id,
                'description' => 'Carga Masiva',
                'code' => 'CARGA-MASIVA-' . $companies_id,
            ]);
        }

        $created = 0;
        $errors = [];

        DB::transaction(function () use ($productsData, $companies_id, $baseCurrency, $department, &$created, &$errors) {
            foreach ($productsData as $index => $productData) {
                try {
                    $data = [
                        'companies_id' => $companies_id,
                        'name' => $productData['name'],
                        'code' => $productData['code'] ?? null,
                        'reference' => $productData['reference'] ?? null,
                        'description' => $productData['description'] ?? null,
                        'cost' => $productData['cost'] ?? 0,
                        'base_unit' => $productData['base_unit'] ?? 'unit',
                        'currency_id' => $baseCurrency->id,
                        'department_id' => $department->id,
                        'is_decimal' => false, // Asumir no decimal por defecto
                        'stock' => 0,
                    ];

                    // Verificar si el code ya existe para esta empresa
                    if ($data['code']) {
                        $existingProduct = Product::where('companies_id', $companies_id)
                            ->where('code', $data['code'])
                            ->first();

                        if ($existingProduct) {
                            // Actualizar el producto existente
                            $existingProduct->update($data);
                            $created++; // Contar como creado/actualizado
                            continue;
                        }
                    }

                    // Crear nuevo producto
                    Product::create($data);
                    $created++;

                } catch (\Exception $e) {
                    $errors[] = "Producto en índice {$index}: " . $e->getMessage();
                }
            }
        });

        return [
            'created' => $created,
            'errors' => $errors
        ];
    }
}