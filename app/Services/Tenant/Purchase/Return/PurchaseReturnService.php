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
            ->with(['items', 'supplier'])
            ->when(
                $request->filled('purchase_return_number'),
                fn($q) =>
                $q->where(
                    'purchase_return_number',
                    'like',
                    "%{$request->purchase_return_number}%"
                )
            )
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {

                    $q->orWhere('purchase_return_number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhere('returned_by', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $q->orWhere('total', $search)
                            ->orWhere('sub_total', $search);
                    }

                    $q->orWhereHas('supplier', function ($supplier) use ($search) {
                        $supplier->where('name', 'like', "%{$search}%")
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
            ->when($request->filled('returned_by'), fn($q) => $q->where('returned_by', 'like', "%{$request->returned_by}%"))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('return_date'), fn($q) => $q->whereDate('return_date', $request->return_date))
            ->when($request->filled('return_miti'), fn($q) => $q->whereDate('return_miti', $request->return_miti))
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('return_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($query) use ($request) {
                $query->whereDate('return_date', '<=', $request->date_upto);
            })
            ->when($request->filled('miti_from'), function ($query) use ($request) {
                $query->where('return_miti', '>=', $request->miti_from);
            })
            ->when($request->filled('miti_upto'), function ($query) use ($request) {
                $query->where('return_miti', '<=', $request->miti_upto);
            })
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
        $purchase_return = $this->purchase_return->with(['items', 'supplier'])->find($id);

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
            return DB::transaction(function () use ($id) {

                $purchaseReturn = $this->purchase_return->find($id);

                if (!$purchaseReturn) {
                    return false;
                }

                LedgerService::deleteByReference(
                    'purchase_return',
                    $purchaseReturn->id
                );

                return $purchaseReturn->delete();
            });
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
