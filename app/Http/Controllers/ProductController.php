<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 🔹 Listar productos (solo datos básicos, sin relaciones pesadas)
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->companies_id) {
            return response()->json(['message' => 'No tienes empresa asociada.'], 403);
        }

        // decides cuántos por página: puedes dejar fijo o permitir query param
        $perPage = $request->input('per_page', 15);  // 15 por página por defecto

        $products = Product::where('companies_id', $user->companies_id)
                        ->with('department') // si tienes relación
                        ->paginate($perPage);

        return response()->json([
            'message' => 'Productos obtenidos correctamente ✅',
            'products' => $products
        ], 200);
    }

    // 🔹 Crear un producto
    public function store(Request $request)
    {
        $user = $request->user();
       
        $validated = $request->validate([
            'name' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'code' => 'required|string|unique:products,code',
            'description' => 'nullable|string',
            'cost' => 'required|numeric',
            'base_unit' => 'required|in:unit,service',
        ]);

        // 🔹 Vincular automáticamente con la empresa del usuario
        $validated['companies_id'] = $user->companies_id;

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Producto registrado correctamente ✅',
            'product' => $product
        ], 201);
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
        $product->update($request->all());

        return response()->json([
            'message' => 'Producto actualizado correctamente ✅',
            'product' => $product
        ], 200);
    }

    // 🔹 Eliminar un producto
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente 🗑️'
        ], 200);
    }
}
