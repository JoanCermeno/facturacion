<?php

namespace App\Http\Controllers;

use App\Models\Companies;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompaniesController extends Controller
{
    /**
     * Display the authenticated user's company.
     * GET /api/companies
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function myCompany(Request $request)
    {
        $user = $request->user();

        if (!$user->companies_id) {
            return response()->json(['message' => 'No tienes una compañía asociada.'], 404);
        }

        $company = Companies::find($user->companies_id)->load('users', 'sellers');

        return response()->json($company);
    }

    //actualizar mi compañía
    public function updateMyCompany(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'             => 'required|string|max:255',
            'address'          => 'nullable|string|max:255',
            'phone'            => 'nullable|string|max:255',
            'rif'              => 'nullable|string|max:255',
            'email'            => ['nullable', 'email', Rule::unique('companies')->ignore($user->companies_id)],
            'invoice_sequence' => 'nullable|integer|min:1',
        ]);

        $company = Companies::updateOrCreate(
            ['id' => $user->companies_id],
            $request->only(['name', 'address', 'phone', 'email', 'rif', 'invoice_sequence'])
        );

        if (!$user->companies_id) {
            $user->companies_id = $company->id;
            $user->save();
        }

        return response()->json([
            'message' => 'Datos de la compañía actualizados correctamente ✅',
            'company' => $company
        ]);
    }


    public function show(Companies $company)
    {
        return response()->json($company->load('users', 'sellers'));
    }

    /**
     * Create or update the authenticated user's company information.
     * PUT /api/companies
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