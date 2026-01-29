<?php

namespace App\Http\Resources\Tenant\Invoice\Return;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Tenant\Invoice\Return\Item\InvoiceReturnItemResource;
use Carbon\Carbon;

class InvoiceReturnResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer?->name,
            'return_date' => $this->return_date ? Carbon::parse($this->return_date)->format('Y-m-d') : null,
            'return_miti' => $this->return_miti ? Carbon::parse($this->return_miti)->format('Y-m-d') : null,
            'formatted_return_date' => $this->return_date ? Carbon::parse($this->return_date)->format('d M Y') : null,
            'sales_return_number' => $this->sales_return_number,
            'tax' => $this->tax,
            'sub_total' => $this->sub_total,
            'total' => $this->total,
            'remarks' => $this->remarks,
            'shift' => $this->shift,
            'items' => $this->items
                ? InvoiceReturnItemResource::collection($this->items)
                : [],
        ];
    }
}
