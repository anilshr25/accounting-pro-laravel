<?php

namespace App\Http\Resources\Tenant\Purchase\Return;

use App\Http\Resources\Tenant\Purchase\Return\Item\PurchaseReturnItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseReturnResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'purchase_invoice_number' => $this->purchaseOrder?->purchase_invoice_number,
            'supplier' => $this->supplier?->name,
            'purchase_invoice_number' => $this->purchase_invoice_number,
            'return_date' => $this->order_date?->format('Y-m-d'),
            'formatted_return_date' => $this->order_date?->format('d M Y'),
            'return_date_miti' => $this->order_date_miti,
            'tax' => $this->tax,
            'sub_total' => $this->sub_total,
            'total' => $this->total,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'returned_by' => $this->returned_by,
            'items' => $this->items ? PurchaseReturnItemResource::collection($this->items) : [],
        ];
    }
}
