<?php

namespace App\Services\Tenant\Invoice\Return\Item;

use App\Models\Tenant\Invoice\Return\Item\InvoiceReturnItem;
use App\Http\Resources\Tenant\Invoice\Return\Item\InvoiceReturnItemResource;

class InvoiceReturnItemService
{
    protected $invoice_return_item;
    public function __construct(InvoiceReturnItem $invoice_return_item)
    {
        $this->invoice_return_item = $invoice_return_item;
    }
    public function paginate($request, $limit = 25)
    {
        $invoice_return_item = $this->invoice_return_item
            ->when($request->filled('invoice_return_id'), function ($query) use ($request) {
                $query->where('invoice_return_id', $request->invoice_return_id);
            })
            ->paginate($request->limit ?? $limit);
        return InvoiceReturnItemResource::collection($invoice_return_item);
    }

    public function store($data)
    {
        try {
            return $this->invoice_return_item->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $invoice_return_item = $this->invoice_return_item->find($id);
        if (!$invoice_return_item) {
            return null;
        }
        return $resource ? new InvoiceReturnItemResource($invoice_return_item) : $invoice_return_item;
    }

    public function update($id, $data)
    {
        try {
            $invoice_return_item = $this->find($id);
            if (!$invoice_return_item) {
                return false;
            }
            return $invoice_return_item->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $invoice_return_item = $this->find($id);
            if (!$invoice_return_item) {
                return false;
            }
            return $invoice_return_item->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
