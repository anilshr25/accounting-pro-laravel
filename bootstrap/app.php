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

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
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
            'tenant' => InitializeTenancyByDomain::class,
            'prevent_access_from_central_domains' => PreventAccessFromCentralDomains::class,
            'user' => CheckAuthMiddleware::class,
            'tenant.owner' => EnsureTenantOwner::class,
            'stateful' => EnsureFrontendRequestsAreStateful::class,
        ])->web([
            TransformAPIHeaders::class,
        ])->validateCsrfTokens(['api/*']);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
