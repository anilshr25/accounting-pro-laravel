<?php

namespace App\Http\Resources\Tenant\Balance;

use Illuminate\Http\Resources\Json\JsonResource;

class BalanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'opening_balance' => $this->opening_balance,
            'closing_balance' => $this->closing_balance,
            'shift' => $this->shift,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
