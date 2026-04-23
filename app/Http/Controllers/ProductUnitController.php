<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PriceType;

class ProductUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 2. Validación: ¿Tiene empresa asociada?
        if (!$user->companies_id) {
            return response()->json(['message' => 'No tienes empresa asociada.'], 403);
        }

        // 3. Consulta base con las relaciones
        $query = ProductUnit::query()->with('prices.priceType');

        // 4. Filtro Multitenant: Solo presentaciones de productos de SU empresa
        $query->whereHas('product', function ($q) use ($user) {
            $q->where('companies_id', $user->companies_id);
        });

        // 5. Filtro Opcional: Por ID de producto
        if ($request->has('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        // 6. PAGINACIÓN: Permitimos que el frontend decida cuántos traer (por defecto 15)
        $perPage = $request->input('per_page', 15);
        $paginatedData = $query->paginate($perPage);

        // 7. Respuesta JSON estructurada para el frontend
        return response()->json([
            'message' => 'Presentaciones obtenidas correctamente',
            // Extraemos la data y la meta-información de la paginación
            'data' => $paginatedData->items(),
            'current_page' => $paginatedData->currentPage(),
            'last_page' => $paginatedData->lastPage(),
            'per_page' => $paginatedData->perPage(),
            'total' => $paginatedData->total(),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'unit_type' => 'required|string|max:50',
            'conversion_factor' => 'required|numeric|min:0.01',
        ]);


        $product = Product::findOrFail($validated['product_id']);


        // Check if unit type already exists for this product (optional, but recommended to avoid duplicates)
        $existingUnit = ProductUnit::where('product_id', $product->id)
            ->where('unit_type', $validated['unit_type'])
            ->first();

        if ($existingUnit) {
            return response()->json([
                'message' => 'Esta presentación ya existe para el producto.',
            ], 422);
        }

        // 4. Ejecutar la Transacción
        return DB::transaction(function () use ($validated, $user, $product) {
            // A. Crear la Unidad
            $unit = ProductUnit::create($validated);

            // B. Calcular el costo base de esta nueva presentación
            $costPerUnit = $product->cost * $unit->conversion_factor;

            // C. 🚀 MAGIA DINÁMICA: Traer los tipos de precio DE ESTA EMPRESA
            $priceTypes = PriceType::where('companies_id', $user->companies_id)->get();

            // D. Crear los precios y el historial dinámicamente
            foreach ($priceTypes as $pt) {
                // Como no hay margen por defecto en la tabla, iniciamos en 0%
                $initialProfit = 0;
                $priceUsd = $costPerUnit * (1 + $initialProfit / 100);

                $price = ProductPrice::create([
                    'product_unit_id' => $unit->id,
                    'price_type_id' => $pt->id,
                    'price_usd' => $priceUsd,
                    'profit_percentage' => $initialProfit,
                ]);

                PriceHistory::create([
                    'product_price_id' => $price->id,
                    'user_id' => $user->id,
                    'old_price' => 0,
                    'new_price' => $price->price_usd,
                    'old_profit_percentage' => 0,
                    'new_profit_percentage' => $price->profit_percentage,
                    // Usamos el nombre real del tipo de precio desde la base de datos
                    'change_reason' => "Precio inicial asignado por sistema ({$pt->name})",
                ]);
            }

            return response()->json([
                'message' => 'Presentación registrada con precios dinámicos ✅',
                'product_unit' => $unit->load('prices.priceType')
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unit = ProductUnit::with('prices.priceType')->findOrFail($id);

        return response()->json([
            'message' => 'Presentación obtenida',
            'product_unit' => $unit
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Obtenemos el usuario y verificamos su empresa
        $user = $request->user();

        if (!$user->companies_id) {
            return response()->json(['message' => 'No tienes empresa asociada.'], 403);
        }

        // 1. Buscamos la unidad (Cargamos el producto y los precios de una vez para optimizar)
        $unit = ProductUnit::with(['product', 'prices'])->whereHas('product', function ($query) use ($user) {
            $query->where('companies_id', $user->companies_id);
        })->findOrFail($id);

        // 2. Validación (Agregamos unit_type por si necesitan corregir un error de tipeo)
        $validated = $request->validate([
            'unit_type' => 'sometimes|required|string|max:50',
            'conversion_factor' => 'sometimes|required|numeric|min:0.01',
        ]);

        // 2. Ejecutamos todo dentro de una Transacción
        return DB::transaction(function () use ($validated, $unit, $user) {

            // Guardamos el factor viejo antes de actualizar
            $oldFactor = $unit->conversion_factor;

            // 3. Actualizamos los datos básicos (unit_type y factor)
            $unit->update($validated);

            // 4. 🚀 LA MAGIA: Si el factor de conversión cambió, recalculamos precios
            if (isset($validated['conversion_factor']) && $oldFactor != $validated['conversion_factor']) {

                $newFactor = $validated['conversion_factor'];
                // Costo base del producto * nuevo factor
                $newCostPerUnit = $unit->product->cost * $newFactor;

                // Recorremos todos los precios que tiene esta presentación
                foreach ($unit->prices as $price) {
                    $oldPriceUsd = $price->price_usd;
                    $profitPercentage = $price->profit_percentage; // Mantenemos su margen intacto

                    // Calculamos el nuevo precio de venta
                    $newPriceUsd = $newCostPerUnit * (1 + ($profitPercentage / 100));

                    // Actualizamos el precio
                    $price->update([
                        'price_usd' => $newPriceUsd
                    ]);

                    // Registramos el historial para que el dueño sepa qué pasó
                    PriceHistory::create([
                        'product_price_id' => $price->id,
                        'user_id' => $user->id,
                        'old_price' => $oldPriceUsd,
                        'new_price' => $newPriceUsd,
                        'old_profit_percentage' => $profitPercentage,
                        'new_profit_percentage' => $profitPercentage, // El margen no cambió
                        'change_reason' => "Ajuste automático: El factor de conversión cambió de {$oldFactor} a {$newFactor}",
                    ]);
                }
            }

            return response()->json([
                'message' => 'Presentación actualizada y precios recalculados ✅',
                'product_unit' => $unit->load('prices.priceType')
            ], 200);
        });

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unit = ProductUnit::findOrFail($id);
        $product = Product::findOrFail($unit->product_id);

        if ($product->base_unit === $unit->unit_type) {
            return response()->json([
                'message' => 'No se puede eliminar la presentación base del producto.'
            ], 422);
        }

        // Eliminar precios asociados e historia (debería estar en cascade o manejado aquí)
        // Por la BD probablemente ya tenga cascade, pero si no, tendríamos que eliminar ProductPrices manualmente

        // Asumiendo cascade en ProductPrice y PriceHistory:
        $unit->delete();

        return response()->json([
            'message' => 'Presentación eliminada correctamente 🗑️'
        ], 200);
    }
}
