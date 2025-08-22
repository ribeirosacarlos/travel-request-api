<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Somente alias, não adicionar global!
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\JwtMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                    return response()->json(['error' => 'Token expirado'], 401);
                }

                if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                    return response()->json(['error' => 'Token inválido'], 401);
                }

                if ($e instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
                    return response()->json(['error' => 'Token não fornecido'], 401);
                }

                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return response()->json(['error' => 'Acesso negado'], 403);
                }

                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json(['error' => 'Recurso não encontrado'], 404);
                }

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'error' => 'Dados inválidos',
                        'errors' => $e->errors()
                    ], 422);
                }
            }
        });
    })
    ->create();
