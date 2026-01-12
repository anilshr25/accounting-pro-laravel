<?php

namespace App\Http\Resources\Tenant\Ledger;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LedgerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'party_id' => $this->party_id,
            'party' => $this->party,
            'debit' => $this->debit,
            'credit' => $this->credit,
            'reference_id' => $this->reference_id,
            'reference' => $this->reference,
            'remarks' => $this->remarks,
            'balance' => $this->balance,
        ];
    }
}
