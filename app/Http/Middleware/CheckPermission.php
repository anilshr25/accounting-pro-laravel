<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Role\Role;

class CheckPermission
{
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {

        if (auth('owner')->check()) {
            return $next($request);
        }

        $user = auth('user')->user();

        if (!$user) {
            return response()->json([
                'status' => 'Unauthorized',
                'message' => 'Authentication required.',
            ], 401);
        }

        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return response()->json([
                'status' => 'NO_BUSINESS',
                'message' => 'Please select a business first.',
            ], 403);
        }

        $tenant = DB::connection('central')
            ->table('tenants')
            ->join('tenant_user', 'tenants.id', '=', 'tenant_user.tenant_id')
            ->where('tenants.id', $tenantId)
            ->where('tenant_user.user_id', $user->id)
            ->where('tenant_user.is_active', true)
            ->select('tenants.*', 'tenant_user.role_id')
            ->first();

        if (!$tenant) {
            return response()->json([
                'status' => 'FORBIDDEN',
                'message' => 'You do not have access to this business.',
            ], 403);
        }

        $roleId = $tenant->role_id;

        if (!$roleId) {
            return response()->json([
                'status' => 'FORBIDDEN',
                'message' => 'No role assigned to this user.',
            ], 403);
        }

        $role = Role::with('permissions')->find($roleId);

        if (!$role) {
            return response()->json([
                'status' => 'FORBIDDEN',
                'message' => 'Role not found.',
            ], 403);
        }


        $hasPermission = $role->permissions
            ->contains('name', $permission);

        if (!$hasPermission) {
            return response()->json([
                'status' => 'FORBIDDEN',
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }


        return $next($request);
    }
}
