<?php

namespace App\Http\Resources\Tenant\Purchase\Return;

use App\Http\Resources\Tenant\Purchase\Return\Item\PurchaseReturnItemResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PurchaseReturnResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'purchase_return_number' => $this->purchase_return_number,
            'supplier' => $this->supplier?->name,
            'return_date' => $this->return_date ? Carbon::parse($this->return_date)->format('Y-m-d') : null,
            'return_miti' => $this->return_miti ? Carbon::parse($this->return_miti)->format('Y-m-d') : null,
            'formatted_return_date' => $this->return_date ? Carbon::parse($this->return_date)->format('d M Y') : null,
            'tax' => $this->tax,
            'sub_total' => $this->sub_total,
            'total' => $this->total,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'returned_by' => $this->returned_by,
            'items' => $this->items ? PurchaseReturnItemResource::collection($this->items) : [],
        ];
    }
}
