<?php

namespace App\Http\Middleware;

use App\Models\Tenant\Tenant;
use Closure;
use Illuminate\Http\Request;
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


        tenancy()->initialize($tenant);

        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
