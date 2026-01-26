<?php

namespace App\Http\Resources\Tenant\Invoice\Return;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceReturnResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'return_miti' => $this->return_miti,
            'return_date' => $this->return_date?->format('Y-m-d'),
            'formatted_invoice_date' => $this->invoice_date?->format('d M Y'),
            'tax' => $this->tax,
            'sub_total' => $this->sub_total,
            'total' => $this->total,
            'remarks' => $this->remarks,
            'shift' => $this->shift,
        ];
    }
}
