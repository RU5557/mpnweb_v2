<?php

use App\Http\Middleware\AdminAuthMiddleware;
use App\Services\ErrorLogService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(fn (Middleware $middleware) => $middleware->alias([
        'admin.auth' => AdminAuthMiddleware::class,
    ]))
    ->withExceptions(function (Exceptions $exceptions): void {

        // 1. REPORT - tetap nulis ke storage/logs/laravel.log
        $exceptions->report(function (Throwable $e) {
            Log::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode(),
            ]);
        });

        // 2. RENDER - HANYA 2 PARAMETER (Laravel 12 rule)
        $exceptions->render(function (Throwable $e, Request $request) {

            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            if (config('app.debug') && app()->environment('local')) {
                $statusForDebug = $e instanceof HttpException ? $e->getStatusCode() : 500;
                if ($statusForDebug === 500) {
                    return null; // biar whoops muncul pas ngoding 500
                }
            }

            $statusCode = 500;
            if ($e instanceof HttpException) {
                $statusCode = $e->getStatusCode();
            } elseif ($e instanceof NotFoundHttpException) {
                $statusCode = 404;
            }

            $modularErrors = [401, 403, 404, 419, 429, 503];

            if (in_array($statusCode, $modularErrors)) {

                // Resolve service DI DALAM closure (ini cara yang bener)
                $service = app(ErrorLogService::class);

                if ($statusCode === 503) {
                    $logData = $service->generateMaintenanceLog($request, $e->getMessage() ?: 'Maintenance');

                    return response()->view("errors.{$statusCode}", array_merge([
                        'exception' => $e,
                        'status' => $statusCode,
                    ], $logData), $statusCode);
                }

                return response()->view("errors.{$statusCode}", [
                    'exception' => $e,
                    'status' => $statusCode,
                ], $statusCode);
            }

            // 500
            if ($request->isMethod('GET') || $request->acceptsHtml() || $statusCode === 500) {
                $service = app(ErrorLogService::class); // resolve di dalam
                $logData = $service->generateForView($e, $request);

                return response()->view('errors.500', $logData, 500);
            }

            return null;
        });
    })->create();
