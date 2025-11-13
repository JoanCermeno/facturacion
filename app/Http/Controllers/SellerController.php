<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;// nesesario para la ediccion de vendedores

class SellerController extends Controller
{
    // Crear un nuevo vendedor para la empresa autenticada (solo admin)
    public function store(Request $request)
    {
        $user = $request->user();

        // Verificar que el usuario tenga empresa
        if (!$user->companies_id) {
            return response()->json(['message' => 'Debes tener una empresa registrada antes de añadir vendedores.'], 403);
        }

        // Verificar que el usuario sea admin
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Solo los administradores pueden registrar vendedores.'], 403);
        }

        $data = $request->validate([
            'ci'         => 'required|string|max:12|unique:sellers,ci',
            'name'       => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'commission' => 'required|numeric|min:0|max:100',
            'commission_type' => 'required|in:sale,utility'
        ]);

        // Asociar vendedor a la empresa del admin    
        $data['companies_id'] = $user->companies_id;
  
        $seller = Seller::create($data);
       

        return response()->json([
            'message' => 'Vendedor registrado correctamente ✅',
            'seller'  => $seller,
        ], 201);
    }

    // Listar vendedores de la empresa (admin y cajeros pueden ver)
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->companies_id) {
            return response()->json(['message' => 'No tienes empresa asociada.'], 403);
        }
        // parametros de busqueda
        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);

        $query = Seller::where('companies_id', $user->companies_id);

        if($search){
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('ci', 'like', "%{$search}%");
            });
        }

        $sellers = $query->paginate($perPage);


        return response()->json($sellers);
    }
    // eliminar vendedor
    public function destroy(Seller $seller)
    {
        $seller->delete();

        return response()->json([
            'message' => 'Vendedor eliminado correctamente 🗑️'
        ], 200);
    }
    // Actualizar vendedor
    public function update(Request $request, Seller $seller)
    {
        $user = $request->user();

        // Debe tener empresa
        if (!$user->companies_id) {
            return response()->json(['message' => 'No tienes empresa asociada.'], 403);
        }

        // Solo admin puede editar
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Solo los administradores pueden editar vendedores.'], 403);
        }

        // Validación (importante agregar ignore)
        $data = $request->validate([
            'ci'         => [
                'required',
                'string',
                'max:12',
                Rule::unique('sellers', 'ci')->ignore($seller->id)
            ],
            'name'       => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'commission' => 'required|numeric|min:0|max:100',
        ]);

        // Actualizar
        $seller->update($data);

        return response()->json([
            'message' => 'Vendedor actualizado correctamente ✅',
            'seller'  => $seller
        ], 200);
    }

}
