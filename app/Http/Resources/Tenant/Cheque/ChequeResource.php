<?php

namespace App\Http\Resources\Tenant\Cheque;

use Illuminate\Http\Resources\Json\JsonResource;

class ChequeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'bank_account_id' => $this->bank_account_id,
            'supplier_id' => $this->supplier_id,
            'customer_id' => $this->customer_id,
            'type' => $this->type,
            'cheque_number' => $this->cheque_number,
            'pay_to' => $this->pay_to,
            'amount' => $this->amount,
            'date' => $this->date,
            'miti' => $this->miti,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'bank_name' => $this->bank_name,
        ];
    }
}
