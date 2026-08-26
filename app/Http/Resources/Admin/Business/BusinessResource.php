<?php

namespace App\Http\Resources\Admin\Business;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'tenant' => [
                'id' => $this->tenant?->id,
                'database' => $this->tenant?->tenancy_db_name,
            ],
            'owner' => $this->when(
                $this->relationLoaded('owner') && $this->owner,
                function () {
                    return [
                        'id' => $this->owner->id,
                        'name' => $this->owner->name,
                        'email' => $this->owner->email,
                    ];
                }
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
