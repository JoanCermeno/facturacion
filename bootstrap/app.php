<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware globales o grupos
        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
         use Illuminate\Validation\ValidationException;
    use Illuminate\Database\Eloquent\ModelNotFoundException;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
    use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
    use Illuminate\Auth\AuthenticationException;
    use Throwable;

    // ⚠️ Errores de validación (422)
    $exceptions->render(function (ValidationException $e, $request) {
        return response()->json([
            'message' => 'Errores de validación',
            'errors'  => $e->errors(),
        ], 422);
    });

    // 🔒 No autenticado (401)
    $exceptions->render(function (AuthenticationException $e, $request) {
        return response()->json([
            'message' => 'No autenticado',
        ], 401);
    });

    // 🚫 Prohibido (403)
    $exceptions->render(function (AccessDeniedHttpException $e, $request) {
        return response()->json([
            'message' => 'No tienes permisos para acceder a este recurso',
        ], 403);
    });

    // 🔎 Modelo no encontrado (404)
    $exceptions->render(function (ModelNotFoundException $e, $request) {
        return response()->json([
            'message' => 'Recurso no encontrado',
        ], 404);
    });

    // 📭 Ruta no encontrada (404)
    $exceptions->render(function (NotFoundHttpException $e, $request) {
        return response()->json([
            'message' => 'Ruta no encontrada',
        ], 404);
    });

    // ❌ Método no permitido (405)
    $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
        return response()->json([
            'message' => 'Método HTTP no permitido',
        ], 405);
    });

    // ⚡ Fallback para cualquier otro error (500)
    $exceptions->render(function (Throwable $e, $request) {
        return response()->json([
            'message' => 'Error interno del servidor',
            // ⚠️ Solo para desarrollo, comenta esta línea en producción
            'error'   => $e->getMessage(),
        ], 500);
    });



    })
    ->create();
