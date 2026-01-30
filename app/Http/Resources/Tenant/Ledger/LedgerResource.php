<?php

namespace App\Http\Resources\Tenant\Ledger;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
                'invoice_return'  => $this->reference?->return_miti ? Carbon::parse($this->reference->return_miti)->format('Y-m-d') : null,
                'purchase_return' => $this->reference?->return_miti ? Carbon::parse($this->reference->return_miti)->format('Y-m-d') : null,
                'invoice'         => $this->reference?->invoice_miti ? Carbon::parse($this->reference->invoice_miti)->format('Y-m-d') : null,
                'purchase_order'  => $this->reference?->received_date_miti ? Carbon::parse($this->reference->received_date_miti)->format('Y-m-d') : null,
                'cheque'          => $this->reference?->miti ? Carbon::parse($this->reference->miti)->format('Y-m-d') : null,
                'credit'          => $this->reference?->miti ? Carbon::parse($this->reference->miti)->format('Y-m-d') : null,
                'payment'         => $this->reference?->miti ? Carbon::parse($this->reference->miti)->format('Y-m-d') : null,
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
                'purchase_order'  => $this->reference?->purchase_invoice_number,
                'credit'          => $this->reference?->invoice_no,
                'cheque'          => $this->reference?->cheque_number,
                'payment' => $this->reference?->transaction_id
                    ?? $this->reference?->payment_method_text
                    ?? $this->reference?->status_text
                    ?? null,
                default           => $this->reference?->status_text
                    ?? $this->reference?->payment_method_text
                    ?? null,
            },

            'remarks' => $this->remarks,
            'balance' => $this->balance,
        ];
    }
}
