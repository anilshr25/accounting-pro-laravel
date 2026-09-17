<?php

namespace App\Http\Middleware;

use App\Models\OwnerUser\OwnerUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileOwner
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (! auth('sanctum')->check()) {
            return response()->json([
                'status' => 'UNAUTHORIZED',
                'message' => 'Authentication required.',
            ], 401);
        }

        $user = auth('sanctum')->user();

        if (! $user instanceof OwnerUser) {
            return response()->json([
                'status' => 'FORBIDDEN',
                'message' => 'Only owners can access this resource.',
            ], 403);
        }

        return $next($request);
    }
}
