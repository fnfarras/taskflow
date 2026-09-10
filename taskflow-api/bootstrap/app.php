<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
        |----------------------------------------------------------------------
        | Pastikan semua exception pada API route menghasilkan response JSON
        | dengan format konsisten: { success, message, data }.
        |----------------------------------------------------------------------
        */

        // AuthenticationException → 401 (unauthenticated / token tidak valid)
        $exceptions->render(function (Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Token tidak valid atau tidak ada.',
                    'data'    => null,
                ], 401);
            }
        });

        // ValidationException → 422
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data yang diberikan tidak valid.',
                    'data'    => ['errors' => $e->errors()],
                ], 422);
            }
        });

        // NotFoundHttpException → 404
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource tidak ditemukan.',
                    'data'    => null,
                ], 404);
            }
        });

        // HttpException lain (termasuk 403, 405, 429 dari throttle, dll) → gunakan status code aslinya
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Terjadi kesalahan HTTP.',
                    'data'    => null,
                ], $e->getStatusCode());
            }
        });

        // Exception tak terduga → 500
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => app()->isProduction()
                        ? 'Terjadi kesalahan pada server.'
                        : $e->getMessage(),
                    'data'    => null,
                ], 500);
            }
        });
    })
    ->booted(function () {
        /*
        |----------------------------------------------------------------------
        | Rate Limiter: "login"
        | Batasi 5 percobaan login per menit per IP untuk mencegah brute force.
        |----------------------------------------------------------------------
        */
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan login. Silakan coba lagi setelah 1 menit.',
                    'data'    => null,
                ], 429);
            });
        });

        /*
        |----------------------------------------------------------------------
        | Rate Limiter: "register"
        | Batasi 5 pendaftaran per menit per IP untuk mencegah spam account.
        |----------------------------------------------------------------------
        */
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan registrasi. Silakan coba lagi setelah 1 menit.',
                    'data'    => null,
                ], 429);
            });
        });
    })
    ->create();
