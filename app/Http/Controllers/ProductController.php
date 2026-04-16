<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use \App\Models\Companies;

class ProductController extends Controller
{
    // 🔹 Listar productos (solo datos básicos, sin relaciones pesadas)
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->companies_id) {
            return response()->json(['message' => 'No tienes empresa asociada.'], 403);
        }

        // Parámetros de búsqueda
        $search = $request->input('search'); // puede ser nombre o código
        $perPage = $request->input('per_page', 15);

        $query = Product::where('companies_id', $user->companies_id)
            ->with(['department','currency']);

        // 🔍 Aplicar filtro si viene texto de búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate($perPage);

        return response()->json([
            'message' => 'Productos obtenidos correctamente ✅',
            'products' => $products
        ], 200);
    }

    // 🔹 Crear un producto
    public function store(Request $request)
    {
        $user = $request->user();

        //Validamos si la empresa tiene seteado el campo de auto generar codigos del producto.
        $company = Companies::find($user->companies_id);
        $rules = [
            'name' => 'required|string',
            'department_id' => 'required|integer|exists:departments,id',
            'description' => 'nullable|string',
            'cost' => 'required|numeric',
            'is_decimal' => 'required|boolean',
            'base_unit' => 'required|in:unit,box,pack,pair,dozen,kg,gr,lb,oz,lt,ml,gal,m,cm,mm,inch,sqm,sqft,hour,day,service',
            'currency_id' => 'required|exists:currencies,id',
            'reference' => 'nullable|string',
            'price_usd' => 'nullable|numeric|min:0',
            'profit_percentage' => 'nullable|numeric',
        ];

        if (!$company->auto_code_products) {
            $rules['code'] = 'required|string|unique:products,code';
        }

        $validated = $request->validate($rules);
        $validated['companies_id'] = $user->companies_id;

        return \Illuminate\Support\Facades\DB::transaction(function() use ($validated, $user) {
            $product = Product::create($validated);

            // 1. Crear la Unidad Base
            $unit = \App\Models\ProductUnit::create([
                'product_id' => $product->id,
                'unit_type' => $validated['base_unit'],
                'conversion_factor' => 1,
            ]);

            // 2. Crear los Precios (Contado, Mayor, Crédito)
            // Si el usuario envió un precio/margen específico, lo usamos para el Precio al Contado (ID 1)
            $defaultProfit = $validated['profit_percentage'] ?? 30; // 30% por defecto solicitado por el usuario
            $retailPrice = $validated['price_usd'] ?? ($validated['cost'] * (1 + $defaultProfit / 100));

            $priceTypes = [
                ['id' => 1, 'profit' => $defaultProfit, 'price' => $retailPrice],
                ['id' => 2, 'profit' => 15, 'price' => $validated['cost'] * 1.15],
                ['id' => 3, 'profit' => 50, 'price' => $validated['cost'] * 1.50],
            ];

            foreach ($priceTypes as $pt) {
                $price = \App\Models\ProductPrice::create([
                    'product_unit_id' => $unit->id,
                    'price_type_id' => $pt['id'],
                    'price_usd' => $pt['price'],
                    'profit_percentage' => $pt['profit'],
                ]);

                \App\Models\PriceHistory::create([
                    'product_price_id' => $price->id,
                    'user_id' => $user->id,
                    'old_price' => 0,
                    'new_price' => $price->price_usd,
                    'old_profit_percentage' => 0,
                    'new_profit_percentage' => $price->profit_percentage,
                    'change_reason' => 'Precio inicial al crear producto',
                ]);
            }

            return response()->json([
                'message' => 'Producto registrado con unidades y precios base ✅',
                'product' => $product->load('units.prices')
            ], 201);
        });
    }

    // 🔹 Mostrar un producto en detalle (con unidades y precios)
    public function show(Product $product)
    {
        $product->load([
            'company',
            'department',
            'units.prices.priceType'
        ]);

        return response()->json([ 
            'message' => 'Productos guardados correctamente ✅',
            'product'  => $product    
        ]);
    }

    // 🔹 Actualizar un producto
    public function update(Request $request, Product $product)
    {
        $user = $request->user();
        $company = \App\Models\Companies::find($user->companies_id);

        $rules = [
            'name' => 'sometimes|string',
            'department_id' => 'sometimes|integer|exists:departments,id',
            'description' => 'nullable|string',
            'cost' => 'sometimes|numeric',
            'is_decimal' => 'sometimes|boolean',
            'base_unit' => 'sometimes|in:unit,box,pack,pair,dozen,kg,gr,lb,oz,lt,ml,gal,m,cm,mm,inch,sqm,sqft,hour,day,service',
            'currency_id' => 'sometimes|exists:currencies,id',
            'reference' => 'nullable|string',
        ];

        if (!$company->auto_code_products) {
            // Solo si la empresa NO autogenera códigos, entonces se valida el campo `code`
            $rules['code'] = 'sometimes|string|unique:products,code,' . $product->id;
        }

        $validated = $request->validate($rules);

        $product->update($validated);

        return response()->json([
            'message' => 'Producto actualizado correctamente ✅',
            'product' => $product
        ], 200);
    }

    // 🔹 Eliminar un producto
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Verificar si tiene operaciones de inventario asociadas
        if ($product->hasInventoryOperations()) {
            return response()->json([
                'message' => 'No se puede eliminar este producto porque está asociado a operaciones de inventario.'
            ], 422);
        }

        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente 🗑️'
        ]);
    }

}
