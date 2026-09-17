<?php

namespace App\Http\Middleware;

use App\Models\OwnerUser\OwnerUser;
use App\Models\User\User;
use App\Models\Tenant\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenantFromToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status' => 'UNAUTHORIZED',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $token = $user->currentAccessToken();

        if (! $token) {
            return response()->json([
                'status' => 'UNAUTHORIZED',
                'message' => 'Invalid access token.',
            ], 401);
        }

        $tenantId = $token->selected_tenant_id;

        if (! $tenantId) {
            return response()->json([
                'status' => 'NO_BUSINESS_SELECTED',
                'message' => 'Please select a business first.',
            ], 400);
        }

        if ($user instanceof OwnerUser) {
            $hasAccess = DB::connection('central')
                ->table('business_owners')
                ->join(
                    'businesses',
                    'businesses.id',
                    '=',
                    'business_owners.business_id'
                )
                ->where(
                    'business_owners.owner_user_id',
                    $user->id
                )
                ->where(
                    'businesses.tenant_id',
                    $tenantId
                )
                ->where(
                    'businesses.status',
                    'active'
                )
                ->exists();

            if (! $hasAccess) {
                return response()->json([
                    'status' => 'FORBIDDEN',
                    'message' => 'You do not have access to this business.',
                ], 403);
            }

            $tenant = Tenant::on('central')->find($tenantId);

            if (! $tenant) {
                return response()->json([
                    'status' => 'INVALID_TENANT',
                    'message' => 'Tenant not found.',
                ], 404);
            }

            tenancy()->initialize($tenant);

            $request->attributes->set('tenant', $tenant);

            return $next($request);
        }

        if ($user instanceof User) {

            $hasAccess = DB::connection('central')
                ->table('tenant_user')
                ->join(
                    'tenants',
                    'tenants.id',
                    '=',
                    'tenant_user.tenant_id'
                )
                ->join(
                    'businesses',
                    'businesses.tenant_id',
                    '=',
                    'tenants.id'
                )
                ->where(
                    'tenant_user.user_id',
                    $user->id
                )
                ->where(
                    'tenant_user.tenant_id',
                    $tenantId
                )
                ->where(
                    'tenant_user.is_active',
                    true
                )
                ->where(
                    'businesses.status',
                    'active'
                )
                ->exists();

            if (! $hasAccess) {
                return response()->json([
                    'status' => 'FORBIDDEN',
                    'message' => 'You do not have access to this business.',
                ], 403);
            }

            $tenant = Tenant::on('central')->find($tenantId);

            if (! $tenant) {
                return response()->json([
                    'status' => 'INVALID_TENANT',
                    'message' => 'Tenant not found.',
                ], 404);
            }

            tenancy()->initialize($tenant);

            $request->attributes->set('tenant', $tenant);

            return $next($request);
        }

        return response()->json([
            'status' => 'UNAUTHORIZED',
            'message' => 'Invalid authenticated user.',
        ], 401);
    }
}
