<?php

namespace App\Http\Resources\Tenant\Procurement\Item;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Tenant\Product\ProductResource;

class ProcurementItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'amount' => round($this->amount, 3),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
