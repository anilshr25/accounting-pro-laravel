<?php

namespace App\Services\Tenant\Purchase\Return\Item;

use App\Models\Tenant\Purchase\Return\Item\PurchaseReturnItem;
use App\Http\Resources\Tenant\Purchase\Return\Item\PurchaseReturnItemResource;

class PurchaseReturnItemService
{
    protected $purchase_return_item;
    public function __construct(PurchaseReturnItem $purchase_return_item)
    {
        $this->purchase_return_item = $purchase_return_item;
    }
    public function paginate($request, $limit = 25)
    {
        $purchase_return_item = $this->purchase_return_item
            ->when($request->filled('purchase_return_id'), function ($query) use ($request) {
                $query->where('purchase_return_id', $request->purchase_return_id);
            })
            ->paginate($request->limit ?? $limit);
        return PurchaseReturnItemResource::collection($purchase_return_item);
    }

    public function store($data)
    {
        try {
            return $this->purchase_return_item->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $purchase_return_item = $this->purchase_return_item->find($id);
        if (!$purchase_return_item) {
            return null;
        }
        return $resource ? new PurchaseReturnItemResource($purchase_return_item) : $purchase_return_item;
    }

    public function update($id, $data)
    {
        try {
            $purchase_return_item = $this->find($id);
            if (!$purchase_return_item) {
                return false;
            }
            return $purchase_return_item->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $purchase_return_item = $this->find($id);
            if (!$purchase_return_item) {
                return false;
            }
            return $purchase_return_item->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
