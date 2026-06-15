<?php

namespace App\Services\Tenant\Invoice\Return;

use App\Models\Tenant\Invoice\Return\InvoiceReturn;
use App\Models\Tenant\Invoice\Return\Item\InvoiceReturnItem;
use App\Http\Resources\Tenant\Invoice\Return\InvoiceReturnResource;
use App\Services\Tenant\Ledger\LedgerService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceReturnService
{
    protected $invoice_return;
    protected $InvoiceReturnItem;

    public function __construct(
        InvoiceReturn $invoice_return,
        InvoiceReturnItem $InvoiceReturnItem
    ) {
        $this->invoice_return = $invoice_return;
        $this->InvoiceReturnItem = $InvoiceReturnItem;
    }

    public function paginate($request, $limit = 25)
    {
        $invoice = $this->invoice_return
            ->with('items')
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {

                    $q->orWhere('sales_return_number', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhere('shift', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $q->orWhere('total', $search)
                            ->orWhere('sub_total', $search);
                    }

                    $q->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%")
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

        return InvoiceReturnResource::collection($invoice);
    }

    public function store($data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $items = $data['items'] ?? [];
                unset($data['items']);

                $invoice_return = $this->invoice_return->create($data);
                if (!$invoice_return) {
                    return false;
                }

                $this->syncItems($invoice_return->id, $items);

                LedgerService::postInvoiceReturn($invoice_return);

                return $invoice_return;
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $invoice_return = $this->invoice_return->with('items')->find($id);

        if (!$invoice_return) {
            return null;
        }

        return $invoice_return;
    }

    public function update($id, $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $invoice_return = $this->invoice_return->find($id);
                if (!$invoice_return) {
                    return false;
                }

                $items = $data['items'] ?? [];
                unset($data['items']);

                $updated = $invoice_return->update($data);

                if ($updated && is_array($items)) {
                    $this->syncItems($invoice_return->id, $items);
                }

                LedgerService::postInvoiceReturn($invoice_return);

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

                $invoiceReturn = $this->invoice_return->find($id);

                if (!$invoiceReturn) {
                    throw new \Exception('Invoice return not found');
                }
                LedgerService::deleteByReference(
                    'invoice_return',
                    $invoiceReturn->id
                );

                $invoiceReturn->delete();
            });

            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    protected function syncItems($InvoiceReturnId, array $items)
    {
        $this->InvoiceReturnItem->newQuery()
            ->where('invoice_return_id', $InvoiceReturnId)
            ->delete();

        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            $item['invoice_return_id'] = $InvoiceReturnId;
            $this->InvoiceReturnItem->create($item);
        }
    }

    public function dateWiseSummary($request, $limit = 25)
    {
        $datesPaginator = $this->invoice_return
            ->selectRaw('DATE(return_date) as return_date_only')
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('return_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($q) use ($request) {
                $q->whereDate('return_date', '<=', $request->date_upto);
            })
            ->groupBy('return_date_only')
            ->orderByDesc('return_date_only')
            ->paginate($request->limit ?? $limit);

        $dates = collect($datesPaginator->items())
            ->pluck('return_date_only')
            ->toArray();

        $invoiceReturns = $this->invoice_return
            ->with('items')
            ->whereIn(DB::raw('DATE(return_date)'), $dates)
            ->orderByDesc('return_date')
            ->get()
            ->map(function ($item) {
                $item->return_date_only = Carbon::parse($item->return_date)->format('Y-m-d');

                return $item;
            })
            ->groupBy('return_date_only');

        $data = $invoiceReturns->map(function ($group, $date) {
            return [
                'return_date' => $date,
                'formatted_return_date' => Carbon::parse($date)->format('d M Y'),
                'return_miti' => Carbon::parse($group->first()->return_miti)->format('Y-m-d'),
                'invoice_count' => $group->count(),
                'sub_total' => number_format($group->sum('sub_total'), 2, '.', ''),
                'total' => number_format($group->sum('total'), 2, '.', ''),
                'invoices' => InvoiceReturnResource::collection($group)->resolve(),
            ];
        })->values();

        return [
            'data' => $data,
            'links' => [
                'first' => $datesPaginator->url(1),
                'last' => $datesPaginator->url($datesPaginator->lastPage()),
                'prev' => $datesPaginator->previousPageUrl(),
                'next' => $datesPaginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $datesPaginator->currentPage(),
                'from' => $datesPaginator->firstItem(),
                'last_page' => $datesPaginator->lastPage(),
                'links' => $datesPaginator->linkCollection(),
                'path' => $datesPaginator->path(),
                'per_page' => $datesPaginator->perPage(),
                'to' => $datesPaginator->lastItem(),
                'total' => $datesPaginator->total(),
            ]
        ];
    }
}
