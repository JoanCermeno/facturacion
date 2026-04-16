<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PricingController extends Controller
{
    /**
     * Listar precios de productos con sus unidades.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Product::where('companies_id', $user->companies_id)
            ->with(['units.prices.priceType', 'currency']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Actualizar precios de forma masiva para un producto o múltiples.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'prices' => 'required|array',
            'prices.*.id' => 'required|exists:product_prices,id',
            'prices.*.price_usd' => 'required|numeric|min:0',
            'prices.*.profit_percentage' => 'nullable|numeric',
        ]);

        return DB::transaction(function () use ($request, $user) {
            $updatedPrices = [];

            foreach ($request->prices as $priceData) {
                $price = ProductPrice::findOrFail($priceData['id']);
                
                // Verificar que el producto pertenezca a la empresa del usuario
                $product = $price->unit->product;
                if ($product->companies_id !== $user->companies_id) {
                    continue;
                }

                $oldPrice = $price->price_usd;
                $oldProfit = $price->profit_percentage;

                // Solo registrar si hubo cambios
                if ($oldPrice != $priceData['price_usd'] || $oldProfit != ($priceData['profit_percentage'] ?? $oldProfit)) {
                    
                    $price->update([
                        'price_usd' => $priceData['price_usd'],
                        'profit_percentage' => $priceData['profit_percentage'] ?? $price->profit_percentage,
                    ]);

                    PriceHistory::create([
                        'product_price_id' => $price->id,
                        'user_id' => $user->id,
                        'old_price' => $oldPrice,
                        'new_price' => $price->price_usd,
                        'old_profit_percentage' => $oldProfit,
                        'new_profit_percentage' => $price->profit_percentage,
                        'change_reason' => $request->change_reason ?? 'Actualización manual',
                    ]);

                    $updatedPrices[] = $price->load('priceType', 'unit');
                }
            }

            return response()->json([
                'message' => 'Precios actualizados correctamente ✅',
                'updated_prices' => $updatedPrices
            ]);
        });
    }

    /**
     * Obtener historial de precios.
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $query = PriceHistory::with(['productPrice.unit.product', 'productPrice.priceType', 'user'])
            ->whereHas('productPrice.unit.product', function($q) use ($user) {
                $q->where('companies_id', $user->companies_id);
            })
            ->orderBy('created_at', 'desc');

        if ($request->has('product_id')) {
            $query->whereHas('productPrice.unit', function($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        return response()->json($query->paginate(30));
    }
}
