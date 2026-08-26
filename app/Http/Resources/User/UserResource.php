<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,

            'businesses' => $this->whenLoaded('tenants', function () {
                return $this->tenants->map(function ($tenant) {
                    return [
                        'tenant_id' => $tenant->id,
                        'business_id' => $tenant->business?->id,
                        'business_name' => $tenant->business?->name,

                        'role_id' => $tenant->pivot->role_id,
                        'is_active' => (bool) $tenant->pivot->is_active,
                    ];
                });
            }),
        ];
    }
}
