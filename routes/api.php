<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController; 
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryOperationController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CustomerController;

// Rutas de Autenticación
Route::prefix('auth')->group(function () {
    Route::post('/register',[AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    // 'logout' debe ser un POST y requiere autenticación
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Rutas protegidas que requieren autenticación con Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Ruta para obtener los datos del usuario autenticado (tu propio perfil)
    Route::get('/user', function (Request $request) {
        return response()->json([
            'message' => 'Información del usuario autenticado',
            'user' => $request->user()
        ]);
    });

    // Rutas para gestionar el perfil del usuario autenticado
    // Se recomienda usar un único recurso para el perfil del usuario,
    // y si es necesario actualizar partes específicas, usar métodos PUT/PATCH al recurso principal.
    Route::get('/profile', [ProfileController::class, 'show']); // Mostrar datos del perfil
    Route::put('/profile', [ProfileController::class, 'update']); // Actualizar datos generales del perfil

    // Para actualizaciones específicas como la contraseña o la compañía,
    // puedes mantener rutas anidadas si lo prefieres, o manejarlo dentro de 'update'.
    // Si mantienes rutas separadas, considera usar PATCH si solo modificas una parte.
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    
    // Rutas para la gestión de la compañía del usuario (admin, o duenio de la empresa)
    Route::apiResource('companies', CompaniesController::class)->only(['show','update']);
    Route::get('/my-company', [CompaniesController::class, 'myCompany']);
    Route::put('/my-company', [CompaniesController::class, 'updateMyCompany']);
    Route::post('/companies/logo', [CompaniesController::class, 'uploadLogo']);


    Route::get('/sellers', [SellerController::class, 'index']);   // Listar vendedores
    Route::post('/sellers', [SellerController::class, 'store']); // Crear vendedor
    Route::delete('/sellers/{seller}', [SellerController::class, 'destroy']); // Borrar vendedor
    
    // Rutas para la gestión de los cajeros
    Route::get('/cashiers', [CashierController::class, 'index']);
    Route::post('/cashiers', [CashierController::class, 'store']);


    // Rutas para la gestión de departamentos
    Route::get('/departments', [DepartmentController::class, 'index']);   // Listar departamentos de la empresa
    Route::post('/departments', [DepartmentController::class, 'store']);  // Crear departamento
    Route::put('/departments/{id}', [DepartmentController::class, 'update']); // Editar departamento
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']); // Borrar departamento
    //* estas rutas de aqui abajo fueron creadas con el flag --api, lo cual nos deja todo el crud muy simplifacod

    Route::apiResource('currencies', CurrencyController::class);
    //productos routes
    Route::apiResource('products', ProductController::class);
    Route::apiResource('inventory-operations', InventoryOperationController::class);
    Route::apiResource('payment-methods', PaymentMethodController::class);

    // Rutas para la gestión de clientes
    Route::apiResource('customers', CustomerController::class);

});