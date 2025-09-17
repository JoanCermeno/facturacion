<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    // 📌 Listar todas las monedas
    public function index()
    {
        return response()->json(Currency::all(), 200);
    }

    // 📌 Crear una nueva moneda
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:50|unique:currencies,name',
            'symbol'        => 'required|string|max:5|unique:currencies,symbol',
            'exchange_rate' => 'required|numeric|min:0',
        ]);

        $currency = Currency::create($validated);

        return response()->json($currency, 201);
    }

    // 📌 Mostrar una moneda específica
    public function show(Currency $currency)
    {
        return response()->json($currency, 200);
    }

    // 📌 Actualizar moneda
    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'name'          => 'sometimes|string|max:50|unique:currencies,name,' . $currency->id,
            'symbol'        => 'sometimes|string|max:5|unique:currencies,symbol,' . $currency->id,
            'exchange_rate' => 'sometimes|numeric|min:0',
        ]);

        $currency->update($validated);

        return response()->json($currency, 200);
    }

    // 📌 Eliminar moneda
    public function destroy(Currency $currency)
    {
        $currency->delete();

        return response()->json(null, 204);
    }
}
