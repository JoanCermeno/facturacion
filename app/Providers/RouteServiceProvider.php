<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            // Rutas principales de la API
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            // Rutas de autenticación
            Route::prefix('api') // 👈 igual que las demás
                ->middleware('api')
                ->group(base_path('routes/auth.php'));
        });
    }
}
