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
        $invoice = $this->invoice
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->when($request->filled('invoice_miti'), function ($query) use ($request) {
                $query->where('invoice_miti', 'like', "%{$request->invoice_miti}%");
            })
            ->when($request->filled('invoice_date'), function ($query) use ($request) {
                $query->whereDate('invoice_date', $request->invoice_date);
            })
            ->when($request->filled('payment_type'), function ($query) use ($request) {
                $query->where('payment_type', $request->payment_type);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('shift'), function ($query) use ($request) {
                $query->where('shift', $request->shift);
            })
            ->when($request->filled('sale_return'), function ($query) use ($request) {
                $query->where('sale_return', $request->sale_return);
            })
            ->paginate($request->limit ?? $limit);
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
        if (!$invoice) {
            return null;
        }
        return $resource ? new InvoiceResource($invoice) : $invoice;
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
