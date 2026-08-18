<?php

namespace App\Http\Resources\Tenant\Kye\EmergencyContact;

use Illuminate\Http\Resources\Json\JsonResource;

class KyeEmergencyContactResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'name' => $this->name,
            'relationship' => $this->relationship,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }
}
