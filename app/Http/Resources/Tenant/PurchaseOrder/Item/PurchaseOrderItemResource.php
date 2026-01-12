<?php

namespace App\Http\Resources\Tenant\PurchaseOrder\Item;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'purchase_order_id' => $this->purchase_order_id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'rate' => $this->rate,
            'amount' => $this->amount,
        ];
    }
}
