<?php

namespace App\Http\Resources\Tenant\Customer\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerPaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'date' => $this->date,
            'miti' => $this->miti,
            'amount' => $this->amount,
            'payment_type' => $this->payment_type,
            'shift' => $this->shift,
            'transaction_id' => $this->transaction_id,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
