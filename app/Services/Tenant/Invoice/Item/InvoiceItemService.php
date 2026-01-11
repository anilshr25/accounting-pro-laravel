<?php

namespace App\Services\Tenant\Invoice\Item;

use App\Models\Tenant\Invoice\Item\InvoiceItem;
use App\Http\Resources\Tenant\Invoice\Item\InvoiceItemResource;

class InvoiceItemService
{
    protected $invoice_item;
    public function __construct(InvoiceItem $invoice_item)
    {
        $this->invoice_item = $invoice_item;
    }

    public function paginate($request, $limit = 25)
    {
        $invoice_item = $this->invoice_item->paginate($request->limit ?? $limit);
        return InvoiceItemResource::collection($invoice_item);
    }

    public function store($data)
    {
        try {
            return $this->invoice_item->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $invoice_item = $this->invoice_item->find($id);
        if (!$invoice_item) {
            return null;
        }
        return $resource ? new InvoiceItemResource($invoice_item) : $invoice_item;
    }

    public function update($id, $data)
    {
        try {
            $invoice_item = $this->find($id);
            if (!$invoice_item) {
                return false;
            }
            return $invoice_item->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $invoice_item = $this->find($id);
            if (!$invoice_item) {
                return false;
            }
            return $invoice_item->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
