<?php

namespace App\Http\Resources\Tenant\Scheme\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

class SchemePaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'scheme_id' => $this->scheme_id,
            'date' => $this->date?->format('Y-m-d'),
            'miti' => $this->miti,
            'amount' => $this->amount,
            'remarks' => $this->remarks,
        ];
    }
}
