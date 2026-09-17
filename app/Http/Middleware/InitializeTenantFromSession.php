<?php

namespace App\Http\Middleware;

use App\Models\OwnerUser\OwnerUser;
use App\Models\User\User;
use App\Models\Tenant\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenantFromSession
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $tenantId = $request->session()->get('tenant_id');

        if (! $tenantId) {
            return response()->json([
                'status' => 'NO_TENANT',
                'message' => 'Please select a business first.',
            ], 403);
        }

        if (Auth::guard('owner')->check()) {

            $owner = Auth::guard('owner')->user();

            if (! $owner instanceof OwnerUser) {
                return response()->json([
                    'status' => 'UNAUTHORIZED',
                    'message' => 'Invalid authenticated user.',
                ], 401);
            }

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
                    $owner->id
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
        } elseif (Auth::guard('user')->check()) {

            $user = Auth::guard('user')->user();

            if (! $user instanceof User) {
                return response()->json([
                    'status' => 'UNAUTHORIZED',
                    'message' => 'Invalid authenticated user.',
                ], 401);
            }

            $hasAccess = DB::connection('central')
                ->table('tenant_user')
                ->join(
                    'businesses',
                    'businesses.tenant_id',
                    '=',
                    'tenant_user.tenant_id'
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
        } else {
            return response()->json([
                'status' => 'UNAUTHORIZED',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $tenant = Tenant::on('central')->find($tenantId);

        if (! $tenant) {
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
