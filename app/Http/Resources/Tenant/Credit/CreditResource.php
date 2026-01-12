<?php

namespace App\Http\Resources\Tenant\Credit;

use Illuminate\Http\Resources\Json\JsonResource;

class CreditResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'type' => $this->type,
            'amount' => $this->amount,
            'return_amount' => $this->return_amount,
            'description' => $this->description,
            'date' => $this->date?->format('Y-m-d'),
            'formatted_date' => $this->date?->format('d M Y'),
            'miti' => $this->miti,
            'shift' => $this->shift,
            'status' => $this->status,
        ];
    }
}
