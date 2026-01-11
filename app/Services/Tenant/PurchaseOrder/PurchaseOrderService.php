<?php

namespace App\Services\Tenant\PurchaseOrder;

use App\Models\Tenant\PurchaseOrder\PurchaseOrder;
use App\Http\Resources\Tenant\PurchaseOrder\PurchaseOrderResource;

class PurchaseOrderService
{
    protected $purchase_order;
    public function __construct(PurchaseOrder $purchase_order)
    {
        $this->purchase_order = $purchase_order;
    }

    public function paginate($request, $limit = 25)
    {
        $purchase_order = $this->purchase_order->paginate($request->limit ?? $limit);
        return PurchaseOrderResource::collection($purchase_order);
    }

    public function store($data)
    {
        try {
            return $this->purchase_order->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $purchase_order = $this->purchase_order->find($id);
        if($purchase_order){
            $resource ? new PurchaseOrderResource($resource) : $resource;
        }
        return null;
    }

    public function update($id, $data)
    {
        try {
            $purchase_order = $this->find($id);
            if (!$purchase_order) {
                return false;
            }
            return $purchase_order->update($data);
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
}
