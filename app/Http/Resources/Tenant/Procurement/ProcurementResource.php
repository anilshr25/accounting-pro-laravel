<?php

namespace App\Http\Resources\Tenant\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'order_miti' => $this->order_miti?->format('Y-m-d'),
            'order_created_by' => $this->order_created_by,
            'total_amount' => $this->total_amount,
            'status' => $this->status ?? 'ordered',
            'remarks' => $this->remarks,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return array_merge([
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'amount' => $item->amount,
                    ], $item->product?->only(['id', 'product_name', 'rate', 'unit', 'category']) ?? []);
                });
            }),
        ];
    }
}
