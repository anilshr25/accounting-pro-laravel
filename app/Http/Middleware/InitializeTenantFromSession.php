<?php

namespace App\Http\Middleware;

use App\Models\Tenant\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenantFromSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->session()->get('tenant_id');

        if (!$tenantId) {
            return response()->json([
                'status' => 'NO_TENANT',
                'message' => 'Please select a business first.',
            ], 403);
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json([
                'status' => 'TENANT_NOT_FOUND',
                'message' => 'Selected business tenant was not found.',
            ], 404);
        }

        if (Auth::guard('owner')->check()) {

            $owner = Auth::guard('owner')->user();

            $businesses = call_user_func([$owner, 'businesses']);

            $hasAccess = $businesses
                ->where('businesses.tenant_id', $tenantId)
                ->where('businesses.status', 'active')
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'status' => 'FORBIDDEN',
                    'message' => 'You do not have access to this business.',
                ], 403);
            }
        } elseif (Auth::guard('user')->check()) {

            $user = Auth::guard('user')->user();

            $businesses = call_user_func([$user, 'businesses']);

            $hasAccess = $businesses
                ->where('businesses.tenant_id', $tenantId)
                ->wherePivot('is_active', true)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'status' => 'FORBIDDEN',
                    'message' => 'You do not have access to this business.',
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'UNAUTHORIZED',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        tenancy()->initialize($tenant);

        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
