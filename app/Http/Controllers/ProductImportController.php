<?php

namespace App\Http\Controllers;

use App\Services\ProductImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductImportController extends Controller
{
    protected $productImportService;

    public function __construct(ProductImportService $productImportService)
    {
        $this->productImportService = $productImportService;
    }

    public function import(Request $request)
    {
        $user = $request->user();

        if (!$user->companies_id) {
            return response()->json(['message' => 'No tienes empresa asociada.'], 403);
        }

        $companies_id = $user->companies_id;

        // Validar que el request tenga un array de productos
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.name' => 'required|string',
            'products.*.code' => 'nullable|string',
            'products.*.cost' => 'nullable|numeric',
            'products.*.base_unit' => 'nullable|in:unit,box,pack,pair,dozen,kg,gr,lb,oz,lt,ml,gal,m,cm,mm,inch,sqm,sqft,hour,day,service',
            'products.*.description' => 'nullable|string',
            'products.*.reference' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->productImportService->importProducts($request->products, $companies_id);

            return response()->json([
                'message' => 'Importación completada.',
                'created' => $result['created'],
                'errors' => $result['errors']
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error en la importación: ' . $e->getMessage()
            ], 500);
        }
    }
}