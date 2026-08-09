<?php

namespace App\Http\Resources\Tenant\Supplier;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray($request)
    {
        if ($request->routeIs('tenant.supplier.search')) {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'payment_terms' => $this->payment_terms,
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'opening_balance' => $this->fiscal_opening_balance
                ?? number_format((float) $this->opening_balance, 2, '.', ''),
            'closing_balance' => $this->fiscal_closing_balance
                ?? number_format((float) $this->closing_balance, 2, '.', ''),
            'pan' => $this->pan,
            'payment_terms' => $this->payment_terms,

        ];
    }
}
