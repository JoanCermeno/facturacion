<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class InventoryOperationController extends Controller
{
    // 🔹 Listar operaciones de inventario
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->companies_id) {
            return response()->json(['message' => 'No tienes una compañía asociada.'], 403);
        }

        $query = InventoryOperation::with(['details.product', 'details.unit', 'user'])
            ->where('company_id', $user->companies_id)
            ->orderBy('operation_date', 'desc');

        // 📦 Filtro por tipo de operación (cargo, descargo, ajuste)
        if ($request->has('operation_type') && in_array($request->operation_type, ['cargo', 'descargo', 'ajuste'])) {
            $query->where('operation_type', $request->operation_type);
        }

        // 🔎 Filtro por búsqueda general (responsable o nota)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('responsible', 'like', "%$search%")
                    ->orWhere('note', 'like', "%$search%");
            });
        }

        // 📅 Filtro por rango de fechas
        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('operation_date', [$request->from, $request->to]);
        } elseif ($request->has('from')) {
            $query->whereDate('operation_date', '>=', $request->from);
        } elseif ($request->has('to')) {
            $query->whereDate('operation_date', '<=', $request->to);
        }

        // 🔢 Paginación opcional (10 por defecto)
        $perPage = $request->get('per_page', 10);

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operation_type' => 'required|in:cargo,descargo,ajuste',
            'note' => 'required|string',
            'responsible' => 'required|string',
            'operation_date' => 'required|date_format:Y-m-d',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            // 🚀 NUEVA REGLA: Aceptamos la unidad seleccionada (opcional)
            'items.*.product_unit_id' => 'nullable|integer|exists:product_units,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $user = $request->user();

        if (!$user->companies_id) {
            return response()->json(['message' => 'No tienes una compañía asociada.'], 403);
        }

        $companyId = $user->companies_id;

        try {
            $operation = DB::transaction(function () use ($validated, $user, $companyId) {

                $lastNumber = InventoryOperation::where('company_id', $companyId)
                    ->where('operation_type', $validated['operation_type'])
                    ->max('operation_number');

                $nextNumber = ((int) $lastNumber) + 1;

                $operation = InventoryOperation::create([
                    'operation_number' => $nextNumber,
                    'operation_type' => $validated['operation_type'],
                    'note' => $validated['note'],
                    'responsible' => $validated['responsible'],
                    'company_id' => $companyId,
                    'operation_date' => $validated['operation_date'],
                    'user_id' => $user->id,
                ]);

                foreach ($validated['items'] as $item) {
                    $product = Product::where('id', $item['product_id'])
                        ->where('companies_id', $companyId) // Seguridad Multitenant
                        ->firstOrFail();

                    $quantity = $item['quantity']; // Ej: 5 (bultos)
                    $conversionFactor = 1;         // Por defecto es 1 (Unidad Base)
                    $productUnitId = null;

                    // 🚀 LA MAGIA: Si enviaron una presentación, calculamos su valor real
                    if (isset($item['product_unit_id']) && $item['product_unit_id']) {
                        // Buscamos la unidad asegurando que pertenece a este producto
                        $unit = \App\Models\ProductUnit::where('id', $item['product_unit_id'])
                            ->where('product_id', $product->id)
                            ->firstOrFail();

                        $conversionFactor = $unit->conversion_factor; // Ej: 20
                        $productUnitId = $unit->id;
                    }

                    // Multiplicamos: 5 bultos * 20 = 100 unidades base reales a mover
                    $baseQuantity = $quantity * $conversionFactor;

                    // ⚠️ Validar stock disponible antes del descargo (usando la cantidad base)
                    if ($validated['operation_type'] === 'descargo' && $product->stock < $baseQuantity) {
                        throw new \Exception("El producto '{$product->name}' no tiene stock suficiente. Intentas sacar {$baseQuantity}, pero solo hay {$product->stock}");
                    }

                    // Actualizar stock según tipo de operación
                    switch ($validated['operation_type']) {
                        case 'cargo':
                            $product->stock += $baseQuantity; // Suma 100
                            break;
                        case 'descargo':
                            $product->stock -= $baseQuantity; // Resta 100
                            break;
                        case 'ajuste':
                            $product->stock = $baseQuantity;  // Iguala a 100
                            break;
                    }

                    $product->save();

                    // Guardar el detalle de la operación exactamente como ocurrió
                    $operation->details()->create([
                        'product_id' => $product->id,
                        'product_unit_id' => $productUnitId, // Registramos si fue en Caja, Bulto, etc.
                        'quantity' => $quantity,             // Registramos que fueron "5"
                    ]);
                }

                return $operation;
            });

            return response()->json([
                'message' => 'Operación creada correctamente ✅',
                'operation' => $operation->load('details'),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar el inventario',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(InventoryOperation $inventoryOperation)
    {
        return response()->json($inventoryOperation->load('details'));
    }

}
