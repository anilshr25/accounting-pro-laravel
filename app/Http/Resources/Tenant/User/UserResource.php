<?php

namespace App\Http\Resources\Tenant\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'image' => $this->image,
            'full_name' => $this->full_name,
            'company_name' => $this->company_name,
            'last_logged_in' => $this->last_logged_in,
            'is_active' => $this->is_active,
        ];
    }
}
