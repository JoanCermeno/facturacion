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

    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'symbol' => 'sometimes|string|max:10',
            'exchange_rate' => 'sometimes|numeric|min:0',
            'is_base' => 'sometimes|boolean',
        ]);

        // ⚠️ Validar unicidad del símbolo dentro de la misma empresa (ignorando el actual)
        if (isset($validated['symbol'])) {
            $exists = Currency::where('companies_id', auth()->user()->companies_id)
                ->where('symbol', $validated['symbol'])
                ->where('id', '!=', $currency->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Ya existe otra moneda con este símbolo en tu empresa.'
                ], 422);
            }
        }

        // 🧩 Si el usuario intenta desmarcar la moneda base actual
        if ($currency->is_base && isset($validated['is_base']) && $validated['is_base'] == false) {
            $existsAnotherBase = Currency::where('id', '!=', $currency->id)
                ->where('is_base', true)
                ->exists();

            if (!$existsAnotherBase) {
                return response()->json([
                    'message' => 'Debe existir al menos una moneda base. No puedes dejar todas en falso.'
                ], 422);
            }
        }

        // 🧩 Si se marca una nueva como base, desmarcar las demás
        if (isset($validated['is_base']) && $validated['is_base'] == true) {
            Currency::where('id', '!=', $currency->id)->update(['is_base' => false]);
        }

        $currency->update($validated);

        return response()->json([
            'message' => 'Moneda actualizada correctamente ✅',
            'currency' => $currency
        ]);
    }

    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);

        if ($currency->is_base) {
            return response()->json([
                'message' => 'No puedes eliminar la moneda base del sistema.'
            ], 422);
        }

        $currency->delete();

        return response()->json([
            'message' => 'Moneda eliminada correctamente 🗑️'
        ]);
    }
}
