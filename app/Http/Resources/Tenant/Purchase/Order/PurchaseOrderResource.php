<?php

namespace App\Http\Resources\Tenant\Purchase\Order;

use App\Http\Resources\Tenant\Purchase\Order\Item\PurchaseOrderItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'supplier' => $this->supplier?->name,
            'purchase_invoice_number' => $this->purchase_invoice_number,
            'order_date' => $this->order_date?->format('Y-m-d'),
            'formatted_order_date' => $this->order_date?->format('d M Y'),
            'order_date_miti' => $this->order_date_miti,
            'received_date' => $this->received_date?->format('Y-m-d'),
            'formatted_received_date' => $this->received_date?->format('d M Y'),
            'received_date_miti' => $this->received_date_miti,
            'tax' => $this->tax,
            'sub_total' => $this->sub_total,
            'total' => $this->total,
            'status' => $this->status,
            'received_by' => $this->received_by,
            'items' => $this->items ? PurchaseOrderItemResource::collection($this->items) : [],
        ];
    }
}
