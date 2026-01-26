<?php

namespace App\Services\Tenant\Purchase\Return;

use App\Models\Tenant\Purchase\Return\PurchaseReturn;
use App\Models\Tenant\Purchase\Return\Item\PurchaseReturnItem;
use App\Services\Tenant\Ledger\LedgerService;
use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    protected $purchase_return;
    protected $purchaseReturnItem;

    public function __construct(
        PurchaseReturn $purchase_return,
        PurchaseReturnItem $purchaseReturnItem
    ) {
        $this->purchase_return = $purchase_return;
        $this->purchaseReturnItem = $purchaseReturnItem;
    }

    public function paginate($request, $limit = 25)
    {
        $query = $this->purchase_return
            ->with('purchaseOrder:id,purchase_invoice_number')
            ->when($request->filled('purchase_invoice_number'), function ($q) use ($request) {
                $q->whereHas('purchaseOrder', function ($q2) use ($request) {
                    $q2->where('invoice_number', $request->purchase_invoice_number);
                });
            })
            ->when($request->filled('returned_by'), fn($q) => $q->where('returned_by', 'like', "%{$request->returned_by}%"))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('return_date'), fn($q) => $q->whereDate('return_date', $request->return_date))
            ->orderBy('return_date', 'DESC')
            ->paginate($request->limit ?? $limit);

        return $query;
    }

    public function store($data)
    {
        try {
            return DB::transaction(function () use ($data) {
                if (!empty($data['purchase_invoice_number'])) {
                    $purchaseOrder = \App\Models\Tenant\Purchase\Order\PurchaseOrder::where('purchase_invoice_number', $data['purchase_invoice_number'])->first();
                    if (!$purchaseOrder) {
                        throw new \Exception("Purchase order not found for invoice {$data['purchase_invoice']}");
                    }
                    $data['purchase_order_id'] = $purchaseOrder->id;
                    unset($data['purchase_invoice']); // remove invoice key
                }
                $items = $data['items'] ?? [];
                unset($data['items']);

                $purchase_return = $this->purchase_return->create($data);
                if (!$purchase_return) {
                    return false;
                }

                $this->syncItems($purchase_return->id, $items);

                LedgerService::postPurchaseReturn($purchase_return);

                return $purchase_return;
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $purchase_return = $this->purchase_return->with('items')->find($id);

        if (!$purchase_return) {
            return null;
        }

        return $purchase_return;
    }

    public function update($id, $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $purchase_return = $this->purchase_return->find($id);
                if (!$purchase_return) {
                    return false;
                }

                if (!empty($data['purchase_invoice'])) {
                    $purchaseOrder = \App\Models\Tenant\Purchase\Order\PurchaseOrder::where('invoice_number', $data['purchase_invoice'])->first();
                    if (!$purchaseOrder) {
                        throw new \Exception("Purchase order not found for invoice {$data['purchase_invoice']}");
                    }
                    $data['purchase_order_id'] = $purchaseOrder->id;
                    unset($data['purchase_invoice']);
                }

                $items = $data['items'] ?? [];
                unset($data['items']);

                $updated = $purchase_return->update($data);

                if ($updated && is_array($items)) {
                    $this->syncItems($purchase_return->id, $items);
                }

                LedgerService::postPurchaseReturn($purchase_return);

                return $updated;
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $purchase_return = $this->purchase_return->find($id);
            if (!$purchase_return) {
                return false;
            }

            return $purchase_return->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }

    protected function syncItems($purchaseReturnId, array $items)
    {
        $this->purchaseReturnItem->newQuery()
            ->where('purchase_return_id', $purchaseReturnId)
            ->delete();

        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            $item['purchase_return_id'] = $purchaseReturnId;
            $this->purchaseReturnItem->create($item);
        }
    }
}
