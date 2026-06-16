<?php

namespace App\Http\Resources\Tenant\Kye\Address;

use Illuminate\Http\Resources\Json\JsonResource;

class KyeAddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'kye_id' => $this->kye_id,
            'type' => $this->type,
            'zone' => $this->zone,
            'district' => $this->district,
            'municipality' => $this->municipality,
            'ward_number' => $this->ward_number,
            'plus_code' => $this->plus_code,
            'locality' => $this->locality,
        ];
    }
}
