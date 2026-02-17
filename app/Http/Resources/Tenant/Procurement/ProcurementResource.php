<?php

namespace App\Http\Resources\Tenant\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

class ProcurementResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'code'      => str_pad($this->code, 2, '0', STR_PAD_LEFT),
            'name'      => $this->name,
            'unit'      => $this->unit,
            'price'     => $this->price,
            'category'  => $this->category,
            'remarks'   => $this->remarks,
            'status'    => $this->status,
            'created_at'=> $this->created_at,
            'updated_at'=> $this->updated_at,
        ];
    }
}
