<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\TransformAPIHeaders;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            'prevent_access_from_central_domains' => \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            'user' => \App\Http\Middleware\CheckAuthMiddleware::class,
            'stateful' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ])->web([
            TransformAPIHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
