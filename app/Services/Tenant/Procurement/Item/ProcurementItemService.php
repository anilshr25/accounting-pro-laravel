<?php

namespace App\Services\Tenant\Procurement\Item;

use App\Models\Tenant\Procurement\Item\ProcurementItem;
use App\Http\Resources\Tenant\Procurement\Item\ProcurementItemResource;

class ProcurementItemService
{
    protected $procurement_item;
    public function __construct(ProcurementItem $procurement_item)
    {
        $this->procurement_item = $procurement_item;
    }
    public function paginate($request, $limit = 25)
    {
        $procurement_item = $this->procurement_item
            ->when($request->filled('procurement_id'), function ($query) use ($request) {
                $query->where('procurement_id', $request->procurement_id);
            })
            ->paginate($request->limit ?? $limit);
        return ProcurementItemResource::collection($procurement_item);
    }

    public function store($data)
    {
        try {
            return $this->procurement_item->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $procurement_item = $this->procurement_item->find($id);
        if (!$procurement_item) {
            return null;
        }
        return $resource ? new ProcurementItemResource($procurement_item) : $procurement_item;
    }

    public function update($id, $data)
    {
        try {
            $procurement_item = $this->find($id);
            if (!$procurement_item) {
                return false;
            }
            return $procurement_item->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $procurement_item = $this->find($id);
            if (!$procurement_item) {
                return false;
            }
            return $procurement_item->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
