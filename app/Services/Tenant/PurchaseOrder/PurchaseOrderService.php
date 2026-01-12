<?php

namespace App\Services\Tenant\PurchaseOrder;

use App\Models\Tenant\PurchaseOrder\PurchaseOrder;
use App\Models\Tenant\PurchaseOrder\Ledger\PurchaseOrderLedger;
use App\Http\Resources\Tenant\PurchaseOrder\PurchaseOrderResource;

class PurchaseOrderService
{
    protected $purchase_order;
    protected $purchase_order_ledger;

    public function __construct(PurchaseOrder $purchase_order, PurchaseOrderLedger $purchase_order_ledger)
    {
        $this->purchase_order = $purchase_order;
        $this->purchase_order_ledger = $purchase_order_ledger;
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
            ->paginate($request->limit ?? $limit);
        return PurchaseOrderResource::collection($purchase_order);
    }

    public function store($data)
    {
        try {
            $purchase_order = $this->purchase_order->create($data);
            if (!$purchase_order) {
                return false;
            }
            $this->syncLedger($purchase_order->id, $data);
            return $purchase_order;
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
            $purchase_order = $this->find($id);
            if (!$purchase_order) {
                return false;
            }
            $updated = $purchase_order->update($data);
            if ($updated) {
                $this->syncLedger($purchase_order->id, $data);
            }
            return $updated;
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

    protected function syncLedger($purchaseOrderId, $data)
    {
        $type = $data['type'] ?? 'purchase';
        if ($type !== 'purchase') {
            return;
        }

        $fields = [
            'date',
            'credit',
            'debit',
            'cheque_id',
            'remarks',
            'balance',
        ];
        $ledgerData = array_intersect_key($data, array_flip($fields));
        $ledgerData['purchase_order_id'] = $purchaseOrderId;

        $this->purchase_order_ledger->newQuery()->updateOrCreate(
            ['purchase_order_id' => $purchaseOrderId],
            $ledgerData
        );
    }
}
