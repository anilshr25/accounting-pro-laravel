<?php

namespace App\Http\Resources\Tenant\Supplier;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'opening_balance' => $this->opening_balance,
            'pan' => $this->pan,

        ];
    }
}
