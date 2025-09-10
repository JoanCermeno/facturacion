<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
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

        if (!$user->fk_company) {
            return response()->json(['message' => 'El usuario no tiene una compañía asociada.'], 404);
        }

        $company = Company::find($user->fk_company);

        if (!$company) {
            // Esto podría ocurrir si fk_company apunta a un ID de compañía que ya no existe
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
            'email'            => ['nullable', 'email', Rule::unique('companys')->ignore($user->fk_company)],
            'invoice_sequence' => 'nullable|integer|min:1',
        ]);

        $company = Company::updateOrCreate(
            ['id' => $user->fk_company],
            $request->only(['name', 'address', 'phone', 'email', 'invoice_sequence'])
        );

        // Si el usuario aún no tiene compañía, la asociamos
        if (!$user->fk_company) {
            $user->fk_company = $company->id;
            $user->save();
        }

        return response()->json([
            'message' => 'Datos de la compañía actualizados correctamente ✅',
            'company' => $company
        ]);
    }
}