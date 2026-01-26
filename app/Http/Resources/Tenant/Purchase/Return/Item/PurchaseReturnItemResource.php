<?php

namespace App\Http\Resources\Tenant\Purchase\Return\Item;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseReturnItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'purchase_return_id' => $this->purchase_return_id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'rate' => $this->rate,
            'amount' => $this->amount,
        ];
    }
}
