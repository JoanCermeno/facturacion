<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * DashboardController
 *
 * Centraliza todas las consultas de estadísticas del Dashboard POS.
 *
 * CONTEXTO MONETARIO:
 *   - El campo `sales.total` está almacenado en USD (moneda base).
 *   - El frontend es responsable de convertir a la moneda local
 *     usando el exchange_rate del endpoint /api/currencies.
 *   - Este controlador devuelve SIEMPRE los montos en USD sin conversión.
 *
 * SEGURIDAD MULTI-EMPRESA:
 *   - Todas las queries están acotadas a `companies_id` del usuario autenticado.
 *   - No hay posibilidad de fuga de datos entre empresas.
 */
class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/stats
     *
     * Devuelve en un solo request todos los KPIs necesarios para el dashboard:
     *   - totalSalesToday    : Suma de ventas completadas hoy (USD)
     *   - ordersToday        : Conteo de ventas completadas hoy
     *   - weeklySales        : Totales diarios de la semana en curso (USD)
     *   - topProducts        : Top 5 productos por unidades vendidas (semana en curso)
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $companyId = $user->companies_id;

        if (!$companyId) {
            return response()->json(['message' => 'No tienes empresa asociada.'], 403);
        }

        // ── Rangos de fecha ──────────────────────────────────────────────────
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY); // Semana ISO: Lunes
        $weekEnd = Carbon::now();

        // ── 1. Ventas de hoy (USD) ───────────────────────────────────────────
        //    SUM sobre la columna `total` que el SaleController ya guarda en USD.
        $totalSalesToday = Sale::query()
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('total');

        // ── 2. Órdenes de hoy ────────────────────────────────────────────────
        $ordersToday = Sale::query()
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->count();

        // ── 3. Tendencia semanal ─────────────────────────────────────────────
        //    Agrupa por día natural (DATE) para obtener un registro por día.
        //    El map() convierte el número de día DAYOFWEEK (1=Dom … 7=Sáb)
        //    en la abreviatura en español que espera el frontend.
        //
        //    NOTA SQLite: Si usas SQLite en desarrollo, DAYOFWEEK no existe.
        //    En ese caso reemplaza la rawQuery por: strftime('%w', created_at)
        //    y ajusta el array de días (0=Dom … 6=Sáb).
        $weeklySales = Sale::query()
            ->select([
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('SUM(total) as daily_total'),
            ])
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'asc')
            ->get()
            ->map(function ($row) {
                // Carbon convierte '2026-05-04' a 'lun', 'mar', etc.
                $dayName = Carbon::parse($row->sale_date)->locale('es')->isoFormat('ddd');

                return [
                    'date' => ucfirst($dayName), // Lo capitaliza a 'Lun', 'Mar'
                    'total' => round((float) $row->daily_total, 2),
                ];
            });

        // ── 4. Top 5 productos más vendidos (semana en curso) ────────────────
        //    JOIN: sale_items → sales → products
        //    Filtra por empresa a través de la venta y suma quantities.
        //    Devuelve el nombre del producto y el total de unidades vendidas.
        //
        //    groupBy incluye products.id para evitar ambigüedades en MySQL
        //    con ONLY_FULL_GROUP_BY (modo estricto).
        $topProducts = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('SUM(sale_items.quantity) as quantity_sold'),
            ])
            ->where('sales.company_id', $companyId)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$weekStart, $weekEnd])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'name' => $row->product_name,
                'quantitySold' => (int) $row->quantity_sold,
            ]);

        // ── Respuesta ────────────────────────────────────────────────────────
        return response()->json([
            /**
             * Todos los montos monetarios están en USD (moneda base).
             * El frontend multiplica por el exchange_rate de la moneda
             * local que el usuario tenga configurada.
             */
            'totalSalesToday' => round((float) $totalSalesToday, 2),
            'ordersToday' => (int) $ordersToday,
            'weeklySales' => $weeklySales,
            'topProducts' => $topProducts,
        ]);
    }
}
