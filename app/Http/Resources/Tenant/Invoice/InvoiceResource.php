<?php

namespace App\Http\Resources\Tenant\Invoice;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'invoice_miti' => $this->invoice_miti,
            'invoice_date' => $this->invoice_date?->format('Y-m-d'),
            'formatted_invoice_date' => $this->invoice_date?->format('d M Y'),
            'tax' => $this->tax,
            'sub_total' => $this->sub_total,
            'total' => $this->total,
            'payment_type' => $this->payment_type,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'shift' => $this->shift,
            'sale_return' => $this->sale_return,
        ];
    }
}
