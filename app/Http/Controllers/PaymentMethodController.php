<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->companies_id;

        $methods = PaymentMethod::where('companies_id', $companyId)
            ->with('currency')
            ->get();

        return response()->json($methods);
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->companies_id;

        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'status' => 'in:activo,inactivo',
        ]);

        // Verificar duplicados por empresa
        $exists = PaymentMethod::where('companies_id', $companyId)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ya existe un método de pago con ese código en esta empresa.'], 422);
        }

        $validated['companies_id'] = $companyId;

        $paymentMethod = PaymentMethod::create($validated);

        return response()->json($paymentMethod->load('currency'), 201);
    }

    public function show($id)
    {
        $companyId = auth()->user()->companies_id;

        $method = PaymentMethod::where('companies_id', $companyId)
            ->with('currency')
            ->findOrFail($id);

        return response()->json($method);
    }

    public function update(Request $request, $id)
    {
        $companyId = auth()->user()->companies_id;

        $paymentMethod = PaymentMethod::where('companies_id', $companyId)
            ->findOrFail($id);

        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:50',
            'description' => 'sometimes|required|string|max:255',
            'currency_id' => 'sometimes|required|exists:currencies,id',
            'status' => 'sometimes|in:activo,inactivo',
        ]);

        if (isset($validated['code'])) {
            $exists = PaymentMethod::where('companies_id', $companyId)
                ->where('code', $validated['code'])
                ->where('id', '!=', $paymentMethod->id)
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'Ese código ya está en uso en esta empresa.'], 422);
            }
        }

        $paymentMethod->update($validated);

        return response()->json($paymentMethod->load('currency'));
    }

    public function destroy($id)
    {
        $companyId = auth()->user()->companies_id;

        $paymentMethod = PaymentMethod::where('companies_id', $companyId)
            ->findOrFail($id);

        $paymentMethod->delete();

        return response()->json(['message' => 'Método de pago eliminado correctamente.']);
    }
}
