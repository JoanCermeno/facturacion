<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{
    public function updateExchangeRate(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'exchange_rate' => 'required|numeric|min:0.000001',
        ]);

        // ✅ No permitir cambiar la moneda base aquí
        if ($currency->is_base) {
            return response()->json([
                'message' => 'La tasa de la moneda base no se puede modificar.',
            ], 422);
        }

        $currency->exchange_rate = $validated['exchange_rate'];
        $currency->save();

        return response()->json([
            'message' => 'Tasa de cambio actualizada correctamente ✅',
            'currency' => $currency,
        ]);
    }

    public function setBaseCurrency(Request $request, $id) {
        DB::transaction(function () use ($id) 
        {

            // poner todas las monedas como no base
            Currency::query()->update([
                'is_base' => false,
                'exchange_rate' => 0,
            ]);

            // asignar nueva base
            $currency = Currency::findOrFail($id);
            $currency->is_base = true;
            $currency->exchange_rate = 1;
            $currency->save();
        });

        return response()->json([
            'message' => 'Moneda base actualizada correctamente'
        ], 200);
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $currencies = Currency::where('companies_id', $user->companies_id)->get();

        return response()->json(
            [
                'message' => 'Monedas obtenidas correctamente ✅',
                'currencies' => $currencies,
            ],
            200,
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'symbol' => 'required|string|max:5',
            'exchange_rate' => 'required|numeric|min:0',
            'is_base' => 'boolean',
            'conversion_type' => 'required|in:multiply,divide',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $validated['companies_id'] = $user->companies_id;

        $currency = Currency::create($validated);

        return response()->json(
            [
                'message' => 'Moneda creada correctamente ✅',
                'currency' => $currency,
            ],
            201,
        );
    }

    public function show(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        return response()->json(
            [
                'message' => 'Moneda obtenida correctamente ✅',
                'currency' => $currency,
            ],
            200,
        );
    }

    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'symbol' => 'sometimes|string|max:4',
            'exchange_rate' => 'sometimes|numeric|min:0',
            'is_base' => 'sometimes|boolean',
            'conversion_type' => 'required|in:multiply,divide',

        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $companiesId = $user->companies_id;

        // ✅ Unicidad del símbolo dentro de la empresa
        if (isset($validated['symbol'])) {
            $exists = Currency::where('companies_id', $companiesId)->where('symbol', $validated['symbol'])->where('id', '!=', $currency->id)->exists();

            if ($exists) {
                return response()->json(
                    [
                        'message' => 'Ya existe otra moneda con este símbolo en tu empresa.',
                    ],
                    422,
                );
            }
        }

        // ✅ No permitir dejar sin moneda base
        if ($currency->is_base && isset($validated['is_base']) && $validated['is_base'] == false) {
            $existsAnother = Currency::where('companies_id', $companiesId)->where('id', '!=', $currency->id)->where('is_base', true)->exists();

            if (!$existsAnother) {
                return response()->json(
                    [
                        'message' => 'Debes tener al menos una moneda base.',
                    ],
                    422,
                );
            }
        }

        // ✅ Si se marca esta como base
        if (isset($validated['is_base']) && $validated['is_base'] == true) {
            // Desmarcar las demás monedas
            Currency::where('companies_id', $companiesId)
                ->where('id', '!=', $currency->id)
                ->update(['is_base' => false]);

            // ✅ La moneda base SIEMPRE debe tener exchange_rate = 1
            $validated['exchange_rate'] = 1;
        }

        // ✅ Actualizar la moneda actual
        $currency->update($validated);

        // ✅ Reconfirmar que la moneda base actual tenga rate = 1, por seguridad
        $base = Currency::where('companies_id', $companiesId)->where('is_base', true)->first();

        if ($base) {
            if ($base->exchange_rate != 1) {
                $base->exchange_rate = 1;
                $base->save();
            }
        }

        return response()->json([
            'message' => 'Moneda actualizada correctamente ✅',
            'currency' => $currency,
        ]);
    }
    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);

        if ($currency->is_base) {
            return response()->json(
                [
                    'message' => 'No puedes eliminar la moneda base del sistema.',
                ],
                422,
            );
        }

        $currency->delete();

        return response()->json([
            'message' => 'Moneda eliminada correctamente 🗑️',
        ]);
    }
    public function conversionTable()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $companyId = $user->companies_id;

        // ✅ Obtener la moneda base
        $base = Currency::where('companies_id', $companyId)
            ->where('is_base', true)
            ->first();

        if (!$base) {
            return response()->json([
                'message' => 'No hay moneda base definida.'
            ], 422);
        }

        // ✅ Obtener las demás monedas
        $currencies = Currency::where('companies_id', $companyId)
            ->where('id', '!=', $base->id)
            ->get();

        // ✅ Construir la tabla de conversión
        $result = [];

        foreach ($currencies as $currency) {

            // ✅ Convertir *1 unidad base* → moneda objetivo
            $converted = $currency->convertFromBase(1);

            $result[] = [
                'symbol' => $currency->symbol,
                'name' => $currency->name,
                'conversion_type' => $currency->conversion_type,
                'exchange_rate' => $currency->exchange_rate,
                'equivalent_to_base' => $converted,
                'formatted' => "1 {$base->symbol} = {$converted} {$currency->symbol}"
            ];
        }

        return response()->json([
            'message' => 'Tabla de conversiones generada correctamente ✅',
            'base_currency' => [
                'symbol' => $base->symbol,
                'name' => $base->name,
                'exchange_rate' => 1
            ],
            'conversions' => $result
        ]);
    }

 

}
