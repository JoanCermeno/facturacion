<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;

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

        $sellers = Seller::where('companies_id', $user->companies_id)->get();

        return response()->json($sellers);
    }
}
