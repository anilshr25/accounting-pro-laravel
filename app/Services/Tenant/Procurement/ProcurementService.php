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
        $fiscalYear = $request->input('fiscal_year');

        [$startYear] = explode('/', $fiscalYear);

        $mitiFrom = $startYear . '-04-01';
        $mitiUpto = ($startYear + 1) . '-03-31';
        $procurements = $this->procurement
            ->with(['items.product'])
            ->whereBetween('order_miti', [$mitiFrom, $mitiUpto])

            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {

                    $q->orWhere('order_number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhere('order_created_by', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $q->orWhere('total_amount', $search);
                    }

                    $q->orWhereHas('items.product', function ($item) use ($search) {
                        $item->where('product_name', 'like', "%{$search}%")
                            ->orWhere('unit', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%");

                        if (is_numeric($search)) {
                            $item->orWhere('quantity', $search)
                                ->orWhere('rate', $search)
                                ->orWhere('amount', $search);
                        }
                    });
                });
            })
            ->when($request->filled('order_number'), function ($query) use ($request) {
                $query->where('order_number', $request->order_number);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('order_date'), function ($query) use ($request) {
                $query->whereDate('order_date', $request->order_date);
            })
            ->when($request->filled('order_miti'), function ($query) use ($request) {
                $query->whereDate('order_miti', $request->order_miti);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('order_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($query) use ($request) {
                $query->whereDate('order_date', '<=', $request->date_upto);
            })
            ->when($request->filled('miti_from'), function ($query) use ($request) {
                $query->where('order_miti', '>=', $request->miti_from);
            })
            ->when($request->filled('miti_upto'), function ($query) use ($request) {
                $query->where('order_miti', '<=', $request->miti_upto);
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

                $lastId = $this->procurement->max('id') + 1;
                $data['order_number'] = 'PR' . str_pad($lastId, 3, '0', STR_PAD_LEFT);

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
