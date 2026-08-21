<?php

namespace App\Http\Resources\Tenant\Kye\Experience;

use Illuminate\Http\Resources\Json\JsonResource;

class KyeExperienceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'company_name' => $this->company_name,
            'position' => $this->position,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
        ];
    }
}
