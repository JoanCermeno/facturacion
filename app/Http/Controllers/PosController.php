<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PriceType;
use Illuminate\Http\Request;

class PosController extends Controller
{
    /**
     * Búsqueda ultra rápida para la pantalla de ventas (POS)
     */
    public function searchProducts(Request $request)
    {
        $user = $request->user();

        // 🚀 SEGURIDAD Y LIMPIEZA
        $query = Product::select('id', 'name', 'code', 'base_unit', 'stock', 'is_decimal', 'currency_id', 'companies_id')
            ->where('companies_id', $user->companies_id)
            ->whereHas('units.prices')
            ->with([
                'units' => function ($q) {
                    $q->select('id', 'product_id', 'unit_type', 'conversion_factor');
                },
                'units.prices' => function ($q) {
                    // Solo enviamos el ID del tipo y el monto
                    $q->select('id', 'product_unit_id', 'price_type_id', 'price_usd');
                },
                // 🚀 ELIMINA O COMENTA ESTA LÍNEA DE ABAJO:
                // 'units.prices.priceType:id,name,slug', 

                'currency:id,symbol,exchange_rate'
            ]);

        // 🚀 VELOCIDAD DE BÚSQUEDA
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // 1. Pistola de Código de Barras (Coincidencia exacta es súper rápida)
                $q->where('code', $search)
                    // 2. Búsqueda por teclado (Nombre)
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->limit(15)->get()
        );
    }

    /**
     * Obtener los tipos de precio disponibles para la empresa del usuario.
     * Se usa en el selector global del POS (contado, mayor, etc.)
     */
    public function priceTypes(Request $request)
    {
        $user = $request->user();
        $types = PriceType::where('companies_id', $user->companies_id)
            ->orderBy('id')
            ->get(['id', 'name', 'slug']);
        return response()->json($types);
    }
}