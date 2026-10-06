<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = $request->header('X-Session-Context');

        $cookie = match ($context) {
            'admin' => env(
                'ADMIN_SESSION_COOKIE',
                'accounting_admin_session'
            ),

            'owner' => env(
                'OWNER_SESSION_COOKIE',
                'accounting_owner_session'
            ),

            'user' => env(
                'USER_SESSION_COOKIE',
                'accounting_user_session'
            ),

            default => config('session.cookie'),
        };

        config([
            'session.cookie' => $cookie,
        ]);

        $response = $next($request);

        return $response;
    }
}
