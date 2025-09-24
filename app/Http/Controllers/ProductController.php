<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 🔹 Listar productos (solo datos básicos, sin relaciones pesadas)
    public function index()
    {
        $products = Product::select('id', 'code', 'name', 'description', 'cost_usd', 'base_unit','companies_id', 'department_id' )
            ->get();

        return response()->json([
            'message' => 'Productos obtenidos correctamente ✅',
            'products' => $products
        ]);
    }

    // 🔹 Crear un producto
    public function store(Request $request)
    {
        $product = Product::create($request->all());
        return response()->json($product, 201);
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
        return response()->json($product, 200);
    }

    // 🔹 Eliminar un producto
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }
}
