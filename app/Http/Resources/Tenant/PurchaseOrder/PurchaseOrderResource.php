<?php

namespace App\Http\Resources\Tenant\PurchaseOrder;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'purchase_invoice_number' => $this->purchase_invoice_number,
            'order_date' => $this->order_date?->format('Y-m-d'),
            'formatted_order_date' => $this->order_date?->format('d M Y'),
            'received_date' => $this->received_date?->format('Y-m-d'),
            'formatted_received_date' => $this->received_date?->format('d M Y'),
            'tax' => $this->tax,
            'sub_total' => $this->sub_total,
            'total' => $this->total,
            'status' => $this->status,
            'received_by' => $this->received_by,
            'items' => $this->items
        ];
    }
}
