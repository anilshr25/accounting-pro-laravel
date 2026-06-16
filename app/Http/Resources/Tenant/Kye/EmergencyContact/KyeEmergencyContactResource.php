<?php

namespace App\Http\Resources\Tenant\Kye\EmergencyContact;

use Illuminate\Http\Resources\Json\JsonResource;

class KyeEmergencyContactResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'kye_id' => $this->kye_id,
            'name' => $this->name,
            'relationship' => $this->relationship,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }
}
