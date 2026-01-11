<?php

namespace App\Services\Tenant\Invoice;

use App\Models\Tenant\Invoice\Invoice;
use App\Http\Resources\Tenant\Invoice\InvoiceResource;

class InvoiceService
{
    protected $invoice;
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function paginate($request, $limit = 25)
    {
        $invoice = $this->invoice->paginate($request->limit ?? $limit);
        return InvoiceResource::collection($invoice);
    }

    public function store($data)
    {
        try {
            return $this->invoice->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $invoice = $this->invoice->find($id);
        if($invoice){
            $resource ? new InvoiceResource($resource) : $resource;
        }
        return null;
    }

    public function update($id, $data)
    {
        try {
            $invoice = $this->find($id);
            if (!$invoice) {
                return false;
            }
            return $invoice->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $invoice = $this->find($id);
            if (!$invoice) {
                return false;
            }
            return $invoice->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
