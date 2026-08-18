<?php

namespace App\Http\Resources\Tenant\Kye\Service;

use Illuminate\Http\Resources\Json\JsonResource;

class KyeServiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'department' => $this->department,
            'position' => $this->position,
            'shift' => $this->shift,
            'salary' => $this->salary,
        ];
    }
}
