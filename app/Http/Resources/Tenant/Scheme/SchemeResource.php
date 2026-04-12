<?php

namespace App\Http\Resources\Tenant\Scheme;

use Illuminate\Http\Resources\Json\JsonResource;

class SchemeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier->name,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'start_miti' => $this->start_miti,
            'end_miti' => $this->end_miti,
            'issued_amount' => $this->issued_amount,
            'percentage' => $this->percentage,
            'scheme_type' => $this->scheme_type,
            'status' => $this->status,
            'image' => $this->image
                ? url($this->image)
                : null,
        ];
    }
}
