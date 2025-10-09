<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::where('companies_id', auth()->user()->companies_id)->get();

        return response()->json([
            'message' => 'Monedas obtenidas correctamente ✅',
            'currencies' => $currencies
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'symbol' => 'required|string|max:5',
            'exchange_rate' => 'required|numeric|min:0',
            'is_base' => 'boolean',
        ]);

        $validated['companies_id'] = auth()->user()->companies_id;

        $currency = Currency::create($validated);

        return response()->json([
            'message' => 'Moneda creada correctamente ✅',
            'currency' => $currency
        ], 201);
    }

    public function update(Request $request, Currency $currency)
    {
        // Validar que la moneda pertenece a la empresa del usuario
        if ($currency->companies_id !== auth()->user()->companies_id) {
            return response()->json(['message' => 'No autorizado 🚫'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:50',
            'symbol' => 'sometimes|string|max:5',
            'exchange_rate' => 'sometimes|numeric|min:0',
            'is_base' => 'sometimes|boolean',
        ]);

        // Si la moneda que se actualiza es marcada como base
        if (isset($validated['is_base']) && $validated['is_base'] === true) {
            // Desactivar todas las monedas de la empresa
            Currency::where('companies_id', auth()->user()->companies_id)
                ->update(['is_base' => false, 'exchange_rate' => 0]);

            // Establecer esta como la base
            $validated['exchange_rate'] = 1; // o la tasa que definas como base
        }

        $currency->update($validated);

        return response()->json([
            'message' => 'Moneda actualizada correctamente ✅',
            'currency' => $currency
        ], 200);
    }

    public function destroy(Currency $currency)
    {
        // Validar que la moneda pertenece a la empresa del usuario
        if ($currency->companies_id !== auth()->user()->companies_id) {
            return response()->json(['message' => 'No autorizado 🚫'], 403);
        }

        $currency->delete();

        return response()->json(['message' => 'Moneda eliminada correctamente ✅'], 200);
    }
}
