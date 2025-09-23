<?php

namespace App\Http\Controllers;

use App\Models\Companies;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompaniesController extends Controller
{
    /**
     * Display the authenticated user's company.
     * GET /api/company
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $user = $request->user();
      
        if (!$user->companies_id) {
            return response()->json(['message' => 'El usuario no tiene una compañía asociada.'], 404);
        }
        //devolver con la lista de vendedores y cajeros
        $company = Companies::find($user->companies_id)->load('users')->load('sellers');

        if (!$company) {
            return response()->json(['message' => 'Compañía asociada no encontrada.'], 404);
        }

        return response()->json($company);
    }

    /**
     * Create or update the authenticated user's company information.
     * PUT /api/company
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'             => 'required|string|max:255',
            'address'          => 'nullable|string|max:255', // Nueva columna 'address'
            'phone'            => 'nullable|string|max:255',
            'rif'              => 'nullable|string|max:255',
            'email'            => ['nullable', 'email', Rule::unique('companys')->ignore($user->companies_id)],
            'invoice_sequence' => 'nullable|integer|min:1',
        ]);

        $company = Companies::updateOrCreate(
            ['id' => $user->companies_id],
            $request->only(['name', 'address', 'phone', 'email', 'invoice_sequence'])
        );

        // Si el usuario aún no tiene compañía, la asociamos
        if (!$user->companies_id) {
            $user->companies_id = $company->id;
            $user->save();
        }

        return response()->json([
            'message' => 'Datos de la compañía actualizados correctamente ✅',
            'company' => $company
        ]);
    }
}