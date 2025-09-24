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

        $currency->update($request->all());

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
