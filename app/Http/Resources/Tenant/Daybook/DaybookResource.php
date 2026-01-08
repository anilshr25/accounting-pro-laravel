<?php

namespace App\Http\Resources\Tenant\Daybook;

use Illuminate\Http\Resources\Json\JsonResource;

class DaybookResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'name' => $this->name,
            'amount' => $this->amount,
            'type' => $this->type,
            'total_amount' => $this->total_amount,
            'remarks' => $this->remarks,
        ];
    }
}
