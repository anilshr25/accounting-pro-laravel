<?php

namespace App\Services\Tenant\Invoice;

use App\Models\Tenant\Invoice\Invoice;
use App\Models\Tenant\Invoice\Item\InvoiceItem;
use App\Http\Resources\Tenant\Invoice\InvoiceResource;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    protected $invoice;
    protected $invoiceItem;

    public function __construct(Invoice $invoice, InvoiceItem $invoiceItem)
    {
        $this->invoice = $invoice;
        $this->invoiceItem = $invoiceItem;
    }
    public function paginate($request, $limit = 25)
    {
        $invoice = $this->invoice
            ->with('items')
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {

                    $q->orWhere('payment_type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('shift', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $q->orWhere('total', $search)
                            ->orWhere('sub_total', $search);
                    }

                    $q->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });

                    $q->orWhereHas('items', function ($item) use ($search) {
                        $item->where('description', 'like', "%{$search}%");

                        if (is_numeric($search)) {
                            $item->orWhere('quantity', $search)
                                ->orWhere('rate', $search)
                                ->orWhere('amount', $search);
                        }
                    });
                });
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('invoice_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($query) use ($request) {
                $query->whereDate('invoice_date', '<=', $request->date_upto);
            })
            ->when($request->filled('miti_from'), function ($query) use ($request) {
                $query->where('invoice_miti', '>=', $request->miti_from);
            })
            ->when($request->filled('miti_upto'), function ($query) use ($request) {
                $query->where('invoice_miti', '<=', $request->miti_upto);
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
            ->orderBy('invoice_date', 'DESC')
            ->paginate($request->limit ?? $limit);
        return InvoiceResource::collection($invoice);
    }

    public function store($data)
    {
        try {
            $invoice = $this->invoice->create($data);

            if (isset($data['items']) && is_array($data['items'])) {
                $invoice->items()->createMany($data['items']);
            }

            $invoice->load('items');

            return $invoice;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $invoice = $this->invoice->with(['items'])->find($id);
        if (!$invoice) {
            return null;
        }
        return $resource ? new InvoiceResource($invoice) : $invoice;
    }

    public function update($id, $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $invoice = $this->find($id);
                if (!$invoice) {
                    return false;
                }
                $items = $data['items'] ?? [];
                unset($data['items']);
                $updated = $invoice->update($data);
                if ($updated) {
                    if (is_array($items)) {
                        $this->syncItems($invoice->id, $items);
                    }
                }
                return $updated;
            });
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

    protected function syncItems($invoiceId, array $items)
    {
        $this->invoiceItem->newQuery()
            ->where('invoice_id', $invoiceId)
            ->delete();

        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            $item['invoice_id'] = $invoiceId;
            $this->invoiceItem->create($item);
        }
    }
}
