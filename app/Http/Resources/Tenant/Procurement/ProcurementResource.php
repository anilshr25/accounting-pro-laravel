<?php

namespace App\Http\Resources\Tenant\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Tenant\Procurement\Item\ProcurementItemResource;

class ProcurementResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'order_date' => $this->order_date?->format('Y-m-d'),
            'formatted_order_date' => $this->order_date?->format('d M Y'),
            'order_time' => $this->order_time?->timezone('Asia/Kathmandu')->format('H:i:s'),
            'order_miti' => $this->order_miti,
            'order_created_by' => $this->order_created_by,
            'total_amount' => $this->total_amount,
            'status' => $this->status ?? 'ordered',
            'remarks' => $this->remarks,
            'items' => ProcurementItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
