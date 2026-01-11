<?php

namespace App\Services\Tenant\PurchaseOrder\Item;

use App\Models\Tenant\PurchaseOrder\Item\PurchaseOrderItem;
use App\Http\Resources\Tenant\PurchaseOrder\Item\PurchaseOrderItemResource;

class PurchaseOrderItemService
{
    protected $purchase_order_item;
    public function __construct(PurchaseOrderItem $purchase_order_item)
    {
        $this->purchase_order_item = $purchase_order_item;
    }

    public function paginate($request, $limit = 25)
    {
        $purchase_order_item = $this->purchase_order_item->paginate($request->limit ?? $limit);
        return PurchaseOrderItemResource::collection($purchase_order_item);
    }

    public function store($data)
    {
        try {
            return $this->purchase_order_item->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $purchase_order_item = $this->purchase_order_item->find($id);
        if (!$purchase_order_item) {
            return null;
        }
        return $resource ? new PurchaseOrderItemResource($purchase_order_item) : $purchase_order_item;
    }

    public function update($id, $data)
    {
        try {
            $purchase_order_item = $this->find($id);
            if (!$purchase_order_item) {
                return false;
            }
            return $purchase_order_item->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $purchase_order_item = $this->find($id);
            if (!$purchase_order_item) {
                return false;
            }
            return $purchase_order_item->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
