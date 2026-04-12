<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashRegisterDetail;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    /**
     * Obtener la caja abierta actual del usuario autenticado.
     */
    public function current(Request $request)
    {
        $user = $request->user();

        $register = CashRegister::with(['details.paymentMethod.currency'])
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        return response()->json([
            'cash_register' => $register,
        ]);
    }

    /**
     * Abrir una nueva caja registradora.
     * Recibe un array de montos por método de pago.
     *
     * Body esperado:
     * {
     *   "details": [
     *     { "payment_method_id": 1, "initial_amount": 50.00 },
     *     { "payment_method_id": 2, "initial_amount": 200.00 },
     *   ],
     *   "notes": "Apertura turno mañana"
     * }
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Verificar que no tenga ya una caja abierta
        $existing = CashRegister::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Ya tienes una caja abierta. Ciérrala antes de abrir otra.',
            ], 422);
        }

        $data = $request->validate([
            'details' => 'required|array|min:1',
            'details.*.payment_method_id' => 'required|exists:payment_methods,id',
            'details.*.initial_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Crear la caja
        $register = CashRegister::create([
            'user_id' => $user->id,
            'companies_id' => $user->companies_id,
            'status' => 'open',
            'opened_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        // Crear los detalles (montos por método de pago)
        foreach ($data['details'] as $detail) {
            CashRegisterDetail::create([
                'cash_register_id' => $register->id,
                'payment_method_id' => $detail['payment_method_id'],
                'initial_amount' => $detail['initial_amount'],
            ]);
        }

        $register->load('details.paymentMethod.currency');

        return response()->json([
            'message' => 'Caja abierta exitosamente.',
            'cash_register' => $register,
        ], 201);
    }

    /**
     * Cerrar una caja registradora.
     * Recibe los montos finales reales por método de pago.
     *
     * Body esperado:
     * {
     *   "details": [
     *     { "payment_method_id": 1, "final_amount": 120.00 },
     *     { "payment_method_id": 2, "final_amount": 500.00 },
     *   ],
     *   "notes": "Todo cuadra bien"
     * }
     */
    public function close(Request $request, $id)
    {
        $user = $request->user();

        $register = CashRegister::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->firstOrFail();

        $data = $request->validate([
            'details' => 'required|array|min:1',
            'details.*.payment_method_id' => 'required|exists:payment_methods,id',
            'details.*.final_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Actualizar los detalles con los montos finales
        foreach ($data['details'] as $detail) {
            CashRegisterDetail::where('cash_register_id', $register->id)
                ->where('payment_method_id', $detail['payment_method_id'])
                ->update(['final_amount' => $detail['final_amount']]);
        }

        // Cerrar la caja
        $register->update([
            'status' => 'closed',
            'closed_at' => now(),
            'notes' => $data['notes'] ?? $register->notes,
        ]);

        // Cargar totales de ventas de esta caja
        $register->load(['details.paymentMethod.currency', 'sales']);

        $totalSales = $register->sales->count();
        $totalIncome = $register->sales->sum('total');

        return response()->json([
            'message' => 'Caja cerrada exitosamente.',
            'summary' => [
                'id' => $register->id,
                'opened_at' => $register->opened_at,
                'closed_at' => $register->closed_at,
                'total_sales' => $totalSales,
                'total_income' => $totalIncome,
                'details' => $register->details,
                'notes' => $register->notes,
            ],
        ]);
    }

    /**
     * Historial de cierres de caja del usuario.
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $registers = CashRegister::with(['details.paymentMethod.currency'])
            ->where('user_id', $user->id)
            ->where('status', 'closed')
            ->orderBy('closed_at', 'desc')
            ->paginate(15);

        // Para cada caja, calcular totales de ventas
        $registers->getCollection()->transform(function ($register) {
            $register->total_sales = $register->sales()->count();
            $register->total_income = $register->sales()->sum('total');
            return $register;
        });

        return response()->json($registers);
    }
}
