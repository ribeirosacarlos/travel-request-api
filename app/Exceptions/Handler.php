<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = ['password', 'password_confirmation'];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {});
    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            if ($e instanceof TokenExpiredException) {
                return response()->json(['error' => 'Token expirado'], 401);
            }
            if ($e instanceof TokenInvalidException) {
                return response()->json(['error' => 'Token inválido'], 401);
            }
            if ($e instanceof JWTException) {
                return response()->json(['error' => 'Token não fornecido'], 401);
            }
        }

        return parent::render($request, $e);
    }
}
