<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController; // Asegúrate de importar tu ProfileController
use App\Http\Controllers\CompanyController; 

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
    
    // Rutas para la gestión de la compañía del usuario (admin)
    Route::get('/company', [CompanyController::class, 'show']); // Mostrar datos de la compañía del admin
    Route::put('/company', [CompanyController::class, 'update']); // Crear/Actualizar datos de la compañía del admin


});