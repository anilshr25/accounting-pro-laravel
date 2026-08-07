<?php

namespace App\Services\Tenant\Purchase\Order;

use App\Models\Tenant\Purchase\Order\PurchaseOrder;
use App\Models\Tenant\Purchase\Order\Item\PurchaseOrderItem;
use App\Http\Resources\Tenant\Purchase\Order\PurchaseOrderResource;
use App\Services\Tenant\Ledger\LedgerService;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    protected $purchase_order;
    protected $purchaseOrderItem;

    public function __construct(
        PurchaseOrder $purchase_order,
        PurchaseOrderItem $purchaseOrderItem
    ) {
        $this->purchase_order = $purchase_order;
        $this->purchaseOrderItem = $purchaseOrderItem;
    }

    public function paginate($request, $limit = 25)
    {
        $fiscalYear = $request->input('fiscal_year');

        [$startYear] = explode('/', $fiscalYear);

        $mitiFrom = $startYear . '-04-01';
        $mitiUpto = ($startYear + 1) . '-03-31';
        $purchaseOrder = $this->purchase_order
            ->whereBetween('received_date_miti', [$mitiFrom, $mitiUpto])
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('supplier_id', $request->supplier_id);
            })

            ->when($request->filled('purchase_invoice_number'), function ($query) use ($request) {
                $query->where('purchase_invoice_number', 'like', "%{$request->purchase_invoice_number}%");
            })

            ->when($request->filled('search'), function ($query) use ($request) {

                $search = trim($request->search);

                $query->where(function ($q) use ($search) {

                    $q->where('purchase_invoice_number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('received_by', 'like', "%{$search}%");

                    $q->orWhereHas('supplier', function ($supplier) use ($search) {
                        $supplier->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('pan', 'like', "%{$search}%");
                    });

                    $q->orWhereHas('items', function ($item) use ($search) {
                        $item->where('description', 'like', "%{$search}%");
                    });

                    if (is_numeric($search)) {

                        $q->orWhere('total', $search)
                            ->orWhere('sub_total', $search);

                        $q->orWhereHas('items', function ($item) use ($search) {
                            $item->where('quantity', $search)
                                ->orWhere('rate', $search)
                                ->orWhere('amount', $search);
                        });
                    }
                });
            })
            ->when($request->filled('order_date'), function ($query) use ($request) {
                $query->whereDate('order_date', $request->order_date);
            })
            ->when($request->filled('received_date'), function ($query) use ($request) {
                $query->whereDate('received_date', $request->received_date);
            })
            ->when($request->filled('order_date_miti'), function ($query) use ($request) {
                $query->where('order_date_miti', $request->order_date_miti);
            })
            ->when($request->filled('received_date_miti'), function ($query) use ($request) {
                $query->where('received_date_miti', $request->received_date_miti);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('received_by'), function ($query) use ($request) {
                $query->where('received_by', 'like', "%{$request->received_by}%");
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('received_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($query) use ($request) {
                $query->whereDate('received_date', '<=', $request->date_upto);
            })
            ->when($request->filled('miti_from'), function ($query) use ($request) {
                $query->where('received_date_miti', '>=', $request->miti_from);
            })
            ->when($request->filled('miti_upto'), function ($query) use ($request) {
                $query->where('received_date_miti', '<=', $request->miti_upto);
            })

            ->orderByDesc('order_date')
            ->paginate($request->limit ?? $limit);

        return PurchaseOrderResource::collection($purchaseOrder);
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
            DB::transaction(function () use ($id) {

                $purchaseOrder = $this->find($id);

                if (!$purchaseOrder) {
                    throw new \Exception('Purchase order not found');
                }

                $purchaseOrder->items()->delete();

                LedgerService::deleteByReference(
                    'purchase_order',
                    $purchaseOrder->id
                );

                $purchaseOrder->delete();
            });

            return true;
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
