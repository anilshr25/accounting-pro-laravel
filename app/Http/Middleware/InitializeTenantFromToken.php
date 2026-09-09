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

        /*
        |--------------------------------------------------------------------------
        | Owner
        |--------------------------------------------------------------------------
        |
        | Owner can access any business assigned to them.
        |
        */
        if (
            $user instanceof OwnerUser ||
            ($user instanceof User && $user->user_type === 'owner')
        ) {
            $tenant = DB::connection('central')
                ->table('tenants')
                ->where('id', $tenantId)
                ->first();

            if (! $tenant) {
                return response()->json([
                    'status' => 'INVALID_TENANT',
                    'message' => 'Invalid tenant.',
                ], 404);
            }

            // Convert stdClass to Tenant model
            $tenant = Tenant::on('central')->find($tenantId);

            tenancy()->initialize($tenant);

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Normal Tenant User
        |--------------------------------------------------------------------------
        |
        | Check user's access to the selected tenant from CENTRAL database.
        |
        */
        if ($user instanceof User) {

            $tenant = DB::connection('central')
                ->table('tenants')
                ->join(
                    'tenant_user',
                    'tenants.id',
                    '=',
                    'tenant_user.tenant_id'
                )
                ->where('tenants.id', $tenantId)
                ->where('tenant_user.user_id', $user->id)
                ->where('tenant_user.is_active', true)
                ->select('tenants.id')
                ->first();

            if (! $tenant) {
                return response()->json([
                    'status' => 'FORBIDDEN',
                    'message' => 'You do not have access to this business.',
                ], 403);
            }

            /*
            |--------------------------------------------------------------------------
            | Initialize Stancl Tenancy
            |--------------------------------------------------------------------------
            */
            $tenantModel = Tenant::on('central')->find($tenantId);

            if (! $tenantModel) {
                return response()->json([
                    'status' => 'INVALID_TENANT',
                    'message' => 'Tenant not found.',
                ], 404);
            }

            tenancy()->initialize($tenantModel);

            return $next($request);
        }

        return response()->json([
            'status' => 'UNAUTHORIZED',
            'message' => 'Invalid authenticated user.',
        ], 401);
    }
}
