<?php

namespace App\Http\Resources\Tenant\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'party_id' => $this->party_id,
            'date' => $this->date,
            'miti' => $this->miti,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'shift' => $this->shift,
            'transaction_id' => $this->transaction_id,
            'remarks' => $this->remarks,
        ];
    }
}
