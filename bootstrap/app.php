<?php

use App\Http\Middleware\CheckAuthMiddleware;
use App\Http\Middleware\EnsureTenantOwner;
use App\Http\Middleware\TransformAPIHeaders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Middleware\HandleMultipartPut;
use App\Http\Middleware\CentralAuth;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\InitializeTenantFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('loan:reminder')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->onOneServer();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission.type' => CheckPermission::class,
            'tenant' => InitializeTenancyByDomain::class,
            'prevent_access_from_central_domains' => PreventAccessFromCentralDomains::class,
            'user' => CheckAuthMiddleware::class,
            'tenant.owner' => EnsureTenantOwner::class,
            'stateful' => EnsureFrontendRequestsAreStateful::class,
            'central.auth' => CentralAuth::class,
            'tenant.session' => InitializeTenantFromSession::class,
        ])->web([
            TransformAPIHeaders::class,
            HandleMultipartPut::class,
        ])->validateCsrfTokens(['api/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
