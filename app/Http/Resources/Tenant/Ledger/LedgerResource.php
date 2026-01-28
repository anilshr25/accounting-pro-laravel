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
            'miti' => match ($this->reference_type) {
                'invoice_return'  => $this->reference?->return_miti,
                'purchase_return' => $this->reference?->return_miti,
                'invoice'         => $this->reference?->invoice_miti,
                'purchase_order'        => $this->reference?->received_date_miti,
                'cheque'          => $this->reference?->miti,
                'credit'          => $this->reference?->miti,
                'payment'         => $this->reference?->miti,
                default           => null,
            },
            'formated_date' => $this->date?->format('d M Y'),
            'party_id' => $this->party_id,
            'party' => $this->party?->name,
            'debit' => $this->debit,
            'credit' => $this->credit,
            'reference_id' => $this->reference_id,
            'status' => match ($this->reference_type) {
                'invoice_return'  => $this->reference?->sales_return_number,
                'purchase_return' => $this->reference?->purchase_return_number,
                'invoice'         => $this->reference?->invoice_no,
                'purchase'        => $this->reference?->purchase_no,
                'credit'          => $this->reference?->status,
                default           => $this->reference?->status_text
                    ?? $this->reference?->payment_method_text
                    ?? null,
            },

            'remarks' => $this->remarks,
            'balance' => $this->balance,
        ];
    }
}
