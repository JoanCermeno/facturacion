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

        // Parámetros de búsqueda
        $search = $request->input('search'); // puede ser nombre o código
        $perPage = $request->input('per_page', 15);

        $query = Product::where('companies_id', $user->companies_id)
            ->with('department');

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
       
        $validated = $request->validate([
            'name' => 'required|string',
            'department_id' => 'required|integer|exists:departments,id',
            'code' => 'required|string|unique:products,code',
            'description' => 'nullable|string',
            'cost' => 'required|numeric',
            'base_unit' => 'required|in:unit,box,pack,pair,dozen,kg,gr,lb,oz,lt,ml,gal,m,cm,mm,inch,sqm,sqft,hour,day,service',
            'currency_id' => 'required|exists:currencies,id', // 👈 Para saber a que moneda corresponde el producto
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
