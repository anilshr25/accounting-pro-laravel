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
            'date' => $this->date?->format('Y-m-d'),
            'formated_date' => $this->date?->format('d M Y'),
            'party_id' => $this->party_id,
            'party' => $this->party?->name,
            'debit' => $this->debit,
            'credit' => $this->credit,
            'reference_id' => $this->reference_id,
            'status' => $this->reference?->status_text ?? $this->reference?->payment_method_text ?? $this->reference?->invoice_no ?? null,
            'remarks' => $this->remarks,
            'balance' => $this->balance,
        ];
    }
}
