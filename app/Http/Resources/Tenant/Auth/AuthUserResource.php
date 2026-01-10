<?php

namespace App\Http\Resources\Tenant\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Str;

class AuthUserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'mobile' => $this->mobile,
            'phone' => $this->phone,
            'username' => $this->username,
            'email' => $this->email,
            'address' => $this->address,
            'user_type' => $this->user_type,
            'token' => Str::random(75),
            'is_mfa_enabled' => $this->is_mfa_enabled,
            'is_email_authentication_enabled' => $this->is_email_authentication_enabled,
            'is_active' => $this->is_active,
            'last_logged_in' => formatYearMonthDateTime($this->last_logged_in),
        ];
    }
}
