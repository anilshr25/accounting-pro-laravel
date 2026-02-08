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
            'party_id' => $this->party_id,
            'party' => $this->party->name ?? null,
            'type' => $this->type,
            'cheque_number' => $this->cheque_number,
            'amount' => $this->amount,
            'date' => $this->date?->format('Y-m-d'),
            'formatted_date' => $this->date?->format('d M Y'),
            'miti' => $this->miti,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'bank_name' => $this->bank_name,
        ];
    }
}
