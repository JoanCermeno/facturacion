<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Invoice;
use App\Models\Product;
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
            'payments' => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount' => 'required|numeric|min:0.01',
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
                $product = Product::findOrFail($itemData['product_id']);
                $unitPrice = $product->cost;
                $subtotal = $unitPrice * $itemData['quantity'];
                $total += $subtotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // Descontar stock
                $product->decrement('stock', $itemData['quantity']);
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
                ]);
            }

            // ── 6. Cargar relaciones para la respuesta ────────
            $sale->load([
                'items.product:id,name,code',
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
                    ]),
                    'payments' => $sale->payments->map(fn($p) => [
                        'method_name' => $p->paymentMethod->description,
                        'currency_symbol' => $p->paymentMethod->currency->symbol ?? '',
                        'amount' => $p->amount,
                        'amount_in_base' => $p->amount_in_base,
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
                'items.product:id,name,code',
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
                ]),
                'payments' => $sale->payments->map(fn($p) => [
                    'method_name' => $p->paymentMethod->description,
                    'currency_symbol' => $p->paymentMethod->currency->symbol ?? '',
                    'amount' => $p->amount,
                    'amount_in_base' => $p->amount_in_base,
                ]),
                'total' => $sale->total,
                'created_at' => $sale->created_at,
                'status' => $sale->status,
            ],
        ]);
    }
}
