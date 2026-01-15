<?php

namespace App\Services\Tenant\PurchaseOrder;

use App\Models\Tenant\PurchaseOrder\PurchaseOrder;
use App\Models\Tenant\PurchaseOrder\Item\PurchaseOrderItem;
use App\Http\Resources\Tenant\PurchaseOrder\PurchaseOrderResource;
use App\Services\Tenant\Ledger\LedgerService;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    protected $purchase_order;
    protected $purchaseOrderItem;

    public function __construct(
        PurchaseOrder $purchase_order,
        PurchaseOrderItem $purchaseOrderItem
    )
    {
        $this->purchase_order = $purchase_order;
        $this->purchaseOrderItem = $purchaseOrderItem;
    }
    public function paginate($request, $limit = 25)
    {
        $purchase_order = $this->purchase_order
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('supplier_id', $request->supplier_id);
            })
            ->when($request->filled('purchase_invoice_number'), function ($query) use ($request) {
                $query->where('purchase_invoice_number', 'like', "%{$request->purchase_invoice_number}%");
            })
            ->when($request->filled('info'), function ($query) use ($request) {
                $info = $request->info;
                $query->whereHas('supplier', function ($q) use ($info) {
                    $q->where('name', 'like', "%{$info}%")
                        ->orWhere('email', 'like', "%{$info}%")
                        ->orWhere('phone', 'like', "%{$info}%")
                        ->orWhere('pan', 'like', "%{$info}%");
                });
            })
            ->when($request->filled('order_date'), function ($query) use ($request) {
                $query->whereDate('order_date', $request->order_date);
            })
            ->when($request->filled('received_date'), function ($query) use ($request) {
                $query->whereDate('received_date', $request->received_date);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('received_by'), function ($query) use ($request) {
                $query->where('received_by', 'like', "%{$request->received_by}%");
            })
             ->orderBy('order_date', 'ASC')
            ->paginate($request->limit ?? $limit);
        return PurchaseOrderResource::collection($purchase_order);
    }

    public function store($data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $items = $data['items'] ?? [];
                unset($data['items']);
                $purchase_order = $this->purchase_order->create($data);
                if (!$purchase_order) {
                    return false;
                }
                $this->syncItems($purchase_order->id, $items);
                LedgerService::postPurchaseOrder($purchase_order);
                return $purchase_order;
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $purchase_order = $this->purchase_order->find($id);
        if (!$purchase_order) {
            return null;
        }
        return $resource ? new PurchaseOrderResource($purchase_order) : $purchase_order;
    }

    public function update($id, $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $purchase_order = $this->find($id);
                if (!$purchase_order) {
                    return false;
                }
                $items = $data['items'] ?? [];
                unset($data['items']);
                $updated = $purchase_order->update($data);
                if ($updated) {
                    if (is_array($items)) {
                        $this->syncItems($purchase_order->id, $items);
                    }
                    LedgerService::postPurchaseOrder($purchase_order);
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
            $purchase_order = $this->find($id);
            if (!$purchase_order) {
                return false;
            }
            return $purchase_order->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }

    protected function syncItems($purchaseOrderId, array $items)
    {
        $this->purchaseOrderItem->newQuery()
            ->where('purchase_order_id', $purchaseOrderId)
            ->delete();

        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            $item['purchase_order_id'] = $purchaseOrderId;
            $this->purchaseOrderItem->create($item);
        }
    }
}
