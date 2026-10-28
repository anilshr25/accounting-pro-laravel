<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        $context = match (true) {
            $request->header('X-Session-Context') === 'admin',
            str_starts_with($path, 'api/admin/') => 'admin',

            $request->header('X-Session-Context') === 'owner',
            str_starts_with($path, 'api/owner/') => 'owner',

            $request->header('X-Session-Context') === 'user' => 'user',

            default => null,
        };

        if ($context !== null) {
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
            };

            config(['session.cookie' => $cookie]);
        }

        return $next($request);
    }
}
