<?php

namespace App\Services\Tenant\Invoice\Return;

use App\Models\Tenant\Invoice\Return\InvoiceReturn;
use App\Models\Tenant\Invoice\Return\Item\InvoiceReturnItem;
use App\Services\Tenant\Ledger\LedgerService;
use Illuminate\Support\Facades\DB;

class InvoiceReturnService
{
    protected $invoice_return;
    protected $InvoiceReturnItem;

    public function __construct(
        InvoiceReturn $invoice_return,
        InvoiceReturnItem $InvoiceReturnItem
    ) {
        $this->invoice_return = $invoice_return;
        $this->InvoiceReturnItem = $InvoiceReturnItem;
    }

    public function paginate($request, $limit = 25)
    {
        $query = $this->invoice_return
            ->with('items')
            ->when(
                $request->filled('sales_return_number'),
                fn($q) =>
                $q->where(
                    'sales_return_number',
                    'like',
                    "%{$request->sales_return_number}%"
                )
            )
            ->when($request->filled('customer_id'), fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('info'), function ($query) use ($request) {
                $info = $request->info;
                $query->whereHas('customer', function ($q) use ($info) {
                    $q->where('name', 'like', "%{$info}%")
                        ->orWhere('email', 'like', "%{$info}%")
                        ->orWhere('phone', 'like', "%{$info}%");
                });
            })
            ->when($request->filled('return_date'), fn($q) => $q->whereDate('return_date', $request->return_date))
            ->orderBy('return_date', 'DESC')
            ->paginate($request->limit ?? $limit);

        return $query;
    }

    public function store($data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $items = $data['items'] ?? [];
                unset($data['items']);

                $invoice_return = $this->invoice_return->create($data);
                if (!$invoice_return) {
                    return false;
                }

                $this->syncItems($invoice_return->id, $items);

                LedgerService::postInvoiceReturn($invoice_return);

                return $invoice_return;
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $invoice_return = $this->invoice_return->with('items')->find($id);

        if (!$invoice_return) {
            return null;
        }

        return $invoice_return;
    }

    public function update($id, $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $invoice_return = $this->invoice_return->find($id);
                if (!$invoice_return) {
                    return false;
                }

                $items = $data['items'] ?? [];
                unset($data['items']);

                $updated = $invoice_return->update($data);

                if ($updated && is_array($items)) {
                    $this->syncItems($invoice_return->id, $items);
                }

                LedgerService::postInvoiceReturn($invoice_return);

                return $updated;
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            DB::transaction(function () use ($id) {

                $invoiceReturn = $this->invoice_return->find($id);

                if (!$invoiceReturn) {
                    throw new \Exception('Invoice return not found');
                }
                LedgerService::deleteByReference(
                    'invoice_return',
                    $invoiceReturn->id
                );

                $invoiceReturn->delete();
            });

            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    protected function syncItems($InvoiceReturnId, array $items)
    {
        $this->InvoiceReturnItem->newQuery()
            ->where('invoice_return_id', $InvoiceReturnId)
            ->delete();

        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            $item['invoice_return_id'] = $InvoiceReturnId;
            $this->InvoiceReturnItem->create($item);
        }
    }
}
