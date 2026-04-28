<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\CashRegister;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Listar ventas del cajero actual (o de la empresa si es admin).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Sale::with([
            'customer:id,name',
            'user:id,name',
            'items',
            'payments.paymentMethod.currency',
            'invoice',
        ])
            ->where('company_id', $user->companies_id)
            ->orderBy('created_at', 'desc');

        // Si es cajero, solo sus ventas
        if ($user->isCashier()) {
            $query->where('user_id', $user->id);
        }

        // Búsqueda por número de factura o cliente
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('invoice', function ($iq) use ($search) {
                    $iq->where('invoice_number', 'like', "%{$search}%");
                })
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $sales = $query->paginate(20);

        // Transformar para el frontend
        $sales->getCollection()->transform(function ($sale) {
            return [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice ? 'FAC-' . str_pad($sale->invoice->invoice_number, 6, '0', STR_PAD_LEFT) : null,
                'customer_name' => $sale->customer->name ?? 'Público General',
                'cashier_name' => $sale->user->name ?? null,
                'total' => $sale->total,
                'items_count' => $sale->items->count(),
                'payment_methods' => $sale->payments->map(function ($p) {
                    return $p->paymentMethod->description . ' (' . ($p->paymentMethod->currency->symbol ?? '?') . ')';
                })->unique()->values(),
                'created_at' => $sale->created_at,
                'status' => $sale->status,
            ];
        });

        return response()->json($sales);
    }

    /**
     * Crear una nueva venta.
     *
     * Body esperado:
     * {
     *   "cash_register_id": 1,
     *   "customer_id": null,
     *   "seller_id": null,
     *   "items": [
     *     { "product_id": 5, "quantity": 2 }
     *   ],
     *   "payments": [
     *     { "payment_method_id": 1, "amount": 10.00 },
     *     { "payment_method_id": 3, "amount": 5.00 }
     *   ],
     *   "notes": ""
     * }
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'customer_id' => 'nullable|exists:customers,id',
            'seller_id' => 'nullable|exists:sellers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.product_unit_id' => 'nullable|exists:product_units,id',
            'items.*.price_type_id' => 'nullable|integer',
            'payments' => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'change_payments' => 'nullable|array',
            'change_payments.*.payment_method_id' => 'required_with:change_payments|exists:payment_methods,id',
            'change_payments.*.amount' => 'required_with:change_payments|numeric|min:0.01',
        ]);

        // Verificar que la caja esté abierta y pertenezca al usuario
        $register = CashRegister::where('id', $data['cash_register_id'])
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->firstOrFail();

        return DB::transaction(function () use ($data, $user, $register) {
            $total = 0;

            // ── 1. Generar número de factura ──────────────────
            $company = $user->company;
            $invoiceNumber = $company->invoice_sequence ?? 1;

            $invoice = Invoice::create([
                'company_id' => $user->companies_id,
                'invoice_number' => $invoiceNumber,
                'type' => 'sale',
            ]);

            // Incrementar el correlativo
            $company->increment('invoice_sequence');

            // ── 2. Crear la venta ─────────────────────────────
            $sale = Sale::create([
                'user_id' => $user->id,
                'client_id' => $data['customer_id'] ?? null,
                'seller_id' => $data['seller_id'] ?? null,
                'company_id' => $user->companies_id,
                'invoice_id' => $invoice->id,
                'cash_register_id' => $register->id,
                'commission_percentage' => 0,
                'total' => 0, // Se calcula abajo
                'status' => 'completed',
            ]);


            // ── 3. Crear items y descontar stock ──────────────
            foreach ($data['items'] as $itemData) {
                $product = Product::with('units.prices')->findOrFail($itemData['product_id']);

                // Determinar la unidad (enviada por frontend o primera disponible)
                $selectedUnit = isset($itemData['product_unit_id'])
                    ? $product->units->firstWhere('id', $itemData['product_unit_id'])
                    : $product->units->first();

                // Determinar el tipo de precio (enviado por frontend o default contado)
                $priceTypeId = $itemData['price_type_id'] ?? 1;
                $productPrice = $selectedUnit
                    ? $selectedUnit->prices->where('price_type_id', $priceTypeId)->first()
                    : null;

                $unitPrice = $productPrice ? $productPrice->price_usd : $product->cost;
                $subtotal = $unitPrice * $itemData['quantity'];
                $total += $subtotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_unit_id' => $selectedUnit?->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // 🚀 ARREGLO CRÍTICO: Cálculo matemático real del stock a descontar
                // Si la unidad seleccionada tiene un factor de conversión (ej: Bulto = 24)
                // multiplicamos la cantidad que compró por ese factor.
                $factorConversion = $selectedUnit ? $selectedUnit->conversion_factor : 1;
                $cantidadRealADescontar = $itemData['quantity'] * $factorConversion;

                // Descontar stock
                $product->decrement('stock', $cantidadRealADescontar);
            }

            // ── 4. Actualizar total de la venta ───────────────
            $sale->update(['total' => $total]);

            // ── 5. Registrar pagos (multi-método) ─────────────
            foreach ($data['payments'] as $paymentData) {
                $paymentMethod = \App\Models\PaymentMethod::with('currency')->findOrFail($paymentData['payment_method_id']);
                $currency = $paymentMethod->currency;

                // Convertir a moneda base
                $amountInBase = $currency->is_base
                    ? $paymentData['amount']
                    : $currency->convertToBase($paymentData['amount']);

                SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_method_id' => $paymentData['payment_method_id'],
                    'amount' => $paymentData['amount'],
                    'currency_id' => $currency->id,
                    'amount_in_base' => $amountInBase,
                    'is_change' => false,
                ]);
            }

            // ── 5.1 Registrar pagos de vuelto / cambio ────────
            if (!empty($data['change_payments'])) {
                foreach ($data['change_payments'] as $changeData) {
                    $paymentMethod = \App\Models\PaymentMethod::with('currency')->findOrFail($changeData['payment_method_id']);
                    $currency = $paymentMethod->currency;

                    $amountInBase = $currency->is_base
                        ? $changeData['amount']
                        : $currency->convertToBase($changeData['amount']);

                    SalePayment::create([
                        'sale_id' => $sale->id,
                        'payment_method_id' => $changeData['payment_method_id'],
                        'amount' => $changeData['amount'],
                        'currency_id' => $currency->id,
                        'amount_in_base' => $amountInBase,
                        'is_change' => true,
                    ]);
                }
            }

            // ── 6. Cargar relaciones para la respuesta ────────
            $sale->load([
                'items.product:id,name,code,is_decimal',
                'items.unit:id,unit_type',
                'payments.paymentMethod.currency',
                'customer:id,name',
                'invoice',
            ]);

            return response()->json([
                'message' => 'Venta registrada exitosamente.',
                'sale' => [
                    'id' => $sale->id,
                    'invoice_number' => 'FAC-' . str_pad($invoice->invoice_number, 6, '0', STR_PAD_LEFT),
                    'customer_name' => $sale->customer->name ?? 'Público General',
                    'items' => $sale->items->map(fn($item) => [
                        'id' => $item->id,
                        'product_name' => $item->product->name,
                        'product_code' => $item->product->code,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                        'is_decimal' => (bool) ($item->product->is_decimal ?? false),
                        'unit_type' => $item->unit?->unit_type ?? null,
                    ]),
                    'payments' => $sale->payments->map(fn($p) => [
                        'method_name' => $p->paymentMethod->description,
                        'currency_symbol' => $p->paymentMethod->currency->symbol ?? '',
                        'amount' => $p->amount,
                        'amount_in_base' => $p->amount_in_base,
                        'is_change' => $p->is_change,
                    ]),
                    'total' => $sale->total,
                    'created_at' => $sale->created_at,
                    'status' => $sale->status,
                ],
            ], 201);
        });
    }

    /**
     * Detalle de una venta específica.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $sale = Sale::with([
            'items.product:id,name,code,is_decimal',
            'items.unit:id,unit_type',
            'payments.paymentMethod.currency',
            'customer:id,name',
            'user:id,name',
            'invoice',
        ])
            ->where('company_id', $user->companies_id)
            ->findOrFail($id);

        return response()->json([
            'sale' => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice ? 'FAC-' . str_pad($sale->invoice->invoice_number, 6, '0', STR_PAD_LEFT) : null,
                'customer_name' => $sale->customer->name ?? 'Público General',
                'cashier_name' => $sale->user->name ?? null,
                'items' => $sale->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product->name ?? 'Producto eliminado',
                    'product_code' => $item->product->code ?? '',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                    'is_decimal' => (bool) ($item->product->is_decimal ?? false),
                    'unit_type' => $item->unit?->unit_type ?? null,
                ]),
                'payments' => $sale->payments->map(fn($p) => [
                    'method_name' => $p->paymentMethod->description,
                    'currency_symbol' => $p->paymentMethod->currency->symbol ?? '',
                    'amount' => $p->amount,
                    'amount_in_base' => $p->amount_in_base,
                    'is_change' => $p->is_change,
                ]),
                'total' => $sale->total,
                'created_at' => $sale->created_at,
                'status' => $sale->status,
            ],
        ]);
    }
}
