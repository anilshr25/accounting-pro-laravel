<?php

namespace App\Http\Resources\Tenant\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => str_pad($this->code, 2, '0', STR_PAD_LEFT),
            'product_name' => $this->product_name,
            'unit' => $this->unit,
            'rate' => $this->rate,
            'category'  => $this->category,
            'created_at'=> $this->created_at,
            'updated_at'=> $this->updated_at,
        ];
    }
}
