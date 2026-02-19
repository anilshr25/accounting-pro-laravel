<?php

namespace App\Services\Tenant\Procurement;

use App\Models\Tenant\Procurement\Procurement;
use App\Models\Tenant\Procurement\Item\ProcurementItem;
use App\Http\Resources\Tenant\Procurement\ProcurementResource;
use Illuminate\Support\Facades\DB;

class ProcurementService
{
    protected $procurement;
    protected $procurementItem;

    public function __construct(
        Procurement $procurement,
        ProcurementItem $procurementItem
    ) {
        $this->procurement = $procurement;
        $this->procurementItem = $procurementItem;
    }

    public function paginate($request, $limit = 25)
    {
        $procurements = $this->procurement
            ->with(['items.product'])
            ->when($request->filled('order_number'), function ($query) use ($request) {
                $query->where('order_number', $request->order_number);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('order_date'), function ($query) use ($request) {
                $query->whereDate('order_date', $request->order_date);
            })
            ->orderBy('order_date', 'DESC')
            ->paginate($request->limit ?? $limit);

        return ProcurementResource::collection($procurements);
    }

    public function store($data)
    {
        try {
            return DB::transaction(function () use ($data) {

                $items = $data['items'] ?? [];
                unset($data['items']);

                $data['order_date'] = $data['order_date'] ?? today()->format('Y-m-d');
                $data['order_time'] = $data['order_time'] ?? now()->timezone('Asia/Kathmandu');


                $data['total_amount'] = collect($items)
                    ->sum('amount');

                $procurement = $this->procurement->create($data);

                if (!empty($items)) {
                    $this->syncItems($procurement->id, $items);
                }

                $procurement->load(['items.product']);

                return $procurement;
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $procurement = $this->procurement
            ->with(['items.product'])
            ->find($id);

        if (!$procurement) {
            return null;
        }

        return $resource
            ? new ProcurementResource($procurement)
            : $procurement;
    }

    public function update($id, $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {

                $procurement = $this->find($id);

                if (!$procurement) {
                    return false;
                }

                $items = $data['items'] ?? [];
                unset($data['items']);

                $data['total_amount'] = collect($items)
                    ->sum(fn($item) => $item['quantity'] * $item['amount']);

                $updated = $procurement->update($data);

                if ($updated) {
                    $this->syncItems($procurement->id, $items);
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
            $procurement = $this->find($id);

            if (!$procurement) {
                return false;
            }

            return $procurement->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }

    protected function syncItems($procurementId, array $items)
    {
        $this->procurementItem->newQuery()
            ->where('procurement_id', $procurementId)
            ->delete();

        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            $item['procurement_id'] = $procurementId;
            $this->procurementItem->create($item);
        }
    }
}
