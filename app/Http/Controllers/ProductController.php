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

        //Validamos si la empresa tiene seteado el campo de auto generar codigos del producto.
        $company = Companies::find($user->companies_id);
        $rules = [
            'name' => 'required|string',
            'department_id' => 'required|integer|exists:departments,id',
            'description' => 'nullable|string',
            'cost' => 'required|numeric',
            'base_unit' => 'required|in:unit,box,pack,pair,dozen,kg,gr,lb,oz,lt,ml,gal,m,cm,mm,inch,sqm,sqft,hour,day,service',
            'currency_id' => 'required|exists:currencies,id',
        ];

        if (!$company->auto_code_products) {
            //Definimos las reglas de validación en caso de que sea falso, es decir se debe poner el code de producto
            $rules['code'] = 'required|string|unique:products,code';
        }

        // 🔹 Validamos si la empresa tiene seteado el campo de auto generar codigos del producto.
        $validated = $request->validate($rules);

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
        $user = $request->user();
        $company = \App\Models\Companies::find($user->companies_id);

        $rules = [
            'name' => 'sometimes|string',
            'department_id' => 'sometimes|integer|exists:departments,id',
            'description' => 'nullable|string',
            'cost' => 'sometimes|numeric',
            'base_unit' => 'sometimes|in:unit,box,pack,pair,dozen,kg,gr,lb,oz,lt,ml,gal,m,cm,mm,inch,sqm,sqft,hour,day,service',
            'currency_id' => 'sometimes|exists:currencies,id',
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
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente 🗑️'
        ], 200);
    }
}
