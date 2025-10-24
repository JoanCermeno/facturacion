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
            'auto_code_products' => 'nullable|boolean',
            'auto_code_departments' => 'nullable|boolean',
            'product_code_prefix' => 'nullable|string|max:255',
            'department_code_prefix' => 'nullable|string|max:255',
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


    /// 🔹 Subir logo de la empresa
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:2048',
        ]);
        $user = $request->user();
        $companyId = $user->companies_id;
        $company = Companies::find($companyId);
        $company->logo_path = $request->logo->store('public/logos');
        $company->save();

    
        return response()->json([
            'message' => 'Logo actualizado correctamente',
       
        ]);
    }

}