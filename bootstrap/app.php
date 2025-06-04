<?php

use App\Exceptions\ExceptionHandler;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prependToGroup('api', [
            ForceJsonResponse::class,
        ]);
        $middleware->alias([
            'auth.optional' => \App\Http\Middleware\OptionalAuth::class,
            'admin'         => \App\Http\Middleware\Admin::class,
            'admin.guest'   => \App\Http\Middleware\AdminGuest::class,
            'api.guest'     => \App\Http\Middleware\ApiGuest::class,
            'role'          => \App\Http\Middleware\RoleMiddleware::class,
            'email.verify'  => \App\Http\Middleware\EmailVerify::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $exception, Request $request) {
            return (new ExceptionHandler())->handle($exception, $request);

            // Default rendering for non-JSON requests
            return parent::render($request, $exception);
        });
    })->create();
