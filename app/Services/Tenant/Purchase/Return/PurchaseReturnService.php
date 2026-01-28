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
            ->with('items')
            ->when(
                $request->filled('purchase_return_number'),
                fn($q) =>
                $q->where(
                    'purchase_return_number',
                    'like',
                    "%{$request->purchase_return_number}%"
                )
            )
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('supplier_id', $request->supplier_id);
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
