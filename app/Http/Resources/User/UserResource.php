<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\TenantAccessService;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tenantId = app(TenantAccessService::class)
            ->selectedTenantId($request);

        $tenant = $this->tenants
            ->firstWhere('id', $tenantId);

        $tenantUser = $this->tenantUsers
            ->firstWhere('tenant_id', $tenantId);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $tenantUser?->role?->name,
            'permissions' => $tenantUser?->role?->permissions
                ? $tenantUser->role->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'type' => $permission->type,
                    ];
                })->values()
                : [],

            'business' => [
                'tenant_id' => $tenant?->id,
                'business_id' => $tenant?->business?->id,
                'business_name' => $tenant?->business?->name,
                'is_active' => (bool) ($tenantUser?->is_active ?? false),
            ],
        ];
    }
}
