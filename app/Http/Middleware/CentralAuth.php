<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CentralAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            !Auth::guard('owner')->check() &&
            !Auth::guard('user')->check()
        ) {
            return response()->json([
                'status' => 'Unauthorized',
                'message' => 'Please login first.',
            ], 401);
        }

        return $next($request);
    }
}
