<?php

namespace App\Http\Resources\Tenant\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request)
    {
        if ($request->routeIs('tenant.customer.search')) {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'opening_balance' => $this->opening_balance,
            'closing_balance' => $this->closing_balance,
            'vat' => $this->vat,

        ];
    }
}
