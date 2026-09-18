<?php

namespace App\Http\Resources\Permission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->first()->name,
            'guard_name' => $this->first()->guard_name,

            'permissions' => $this->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'type' => $permission->type,
                ];
            })->values()->toArray(),
        ];
    }
}
