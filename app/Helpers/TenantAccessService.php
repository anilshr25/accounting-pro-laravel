<?php

namespace App\Helpers;

use App\Models\OwnerUser\OwnerUser;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenantAccessService
{
    /**
     * Get the currently selected tenant/business.
     */
    public function selectedTenantId(Request $request): ?string
    {
        if (auth('sanctum')->check()) {

            $user = $request->user();

            if (! $user) {
                return null;
            }

            $token = $user->currentAccessToken();

            if (! $token) {
                return null;
            }

            return $token->selected_tenant_id;
        }

        if (auth('owner')->check() || auth('user')->check()) {
            return session('tenant_id');
        }

        return null;
    }

    /**
     * Ensure the authenticated caller can access the tenant.
     */
    public function ensureCallerCanAccessTenant(
        string $tenantId
    ): void {
        $caller =
            auth('sanctum')->user()
            ?? auth('owner')->user()
            ?? auth('user')->user();

        if (! $caller) {
            throw ValidationException::withMessages([
                'auth' => ['Authentication required.'],
            ]);
        }

        if ($caller instanceof OwnerUser) {

            $allowed = DB::connection('central')
                ->table('business_owners')
                ->join(
                    'businesses',
                    'businesses.id',
                    '=',
                    'business_owners.business_id'
                )
                ->where(
                    'business_owners.owner_user_id',
                    $caller->id
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

            if (! $allowed) {
                throw ValidationException::withMessages([
                    'tenant_id' => [
                        'You do not have access to this business.',
                    ],
                ]);
            }

            return;
        }

        if ($caller instanceof User) {

            $allowed = DB::connection('central')
                ->table('tenant_user')
                ->join(
                    'businesses',
                    'businesses.tenant_id',
                    '=',
                    'tenant_user.tenant_id'
                )
                ->where(
                    'tenant_user.user_id',
                    $caller->id
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

            if (! $allowed) {
                throw ValidationException::withMessages([
                    'tenant_id' => [
                        'You do not have access to this business.',
                    ],
                ]);
            }

            return;
        }

        throw ValidationException::withMessages([
            'auth' => ['Unauthorized.'],
        ]);
    }

    /**
     * Ensure the target user belongs to the selected tenant.
     */
    public function ensureUserBelongsToTenant(
        int|string $userId,
        string $tenantId
    ): User {
        $user = User::query()
            ->where('id', $userId)
            ->whereHas('tenantUsers', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'user' => [
                    'User does not belong to the selected business.',
                ],
            ]);
        }

        return $user;
    }
}
