<?php

namespace App\Http\Resources\Tenant\Kye\Education;

use Illuminate\Http\Resources\Json\JsonResource;

class KyeEducationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'degree' => $this->degree,
            'university' => $this->university,
            'from_date' => $this->from_date->format('Y-m-d'),
            'to_date' => $this->to_date->format('Y-m-d'),
            'locality' => $this->locality,
        ];
    }
}
