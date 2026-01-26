<?php

namespace App\Http\Resources\Tenant\Invoice\Return\Item;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceReturnItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_return_id' => $this->invoice_return_id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'rate' => $this->rate,
            'amount' => $this->amount,
        ];
    }
}
