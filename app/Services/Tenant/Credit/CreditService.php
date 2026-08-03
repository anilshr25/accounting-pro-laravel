<?php

namespace App\Services\Tenant\Credit;

use App\Models\Tenant\Credit\Credit;
use App\Http\Resources\Tenant\Credit\CreditResource;
use App\Services\Tenant\Ledger\LedgerService;
use Illuminate\Support\Facades\DB;


class CreditService
{
    protected $credit;
    public function __construct(Credit $credit)
    {
        $this->credit = $credit;
    }
    public function paginate($request, $limit = 25)
    {
        $query = $this->credit
            ->with('customer:id,name')
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->filled('amount'), function ($query) use ($request) {
                $query->where('amount', $request->amount);
            })
            ->when($request->filled('return_amount'), function ($query) use ($request) {
                $query->where('return_amount', $request->return_amount);
            })
            ->when($request->filled('description'), function ($query) use ($request) {
                $query->where('description', 'like', "%{$request->description}%");
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($query) use ($request) {
                $query->whereDate('date', '<=', $request->date_upto);
            })
            ->when($request->filled('miti_from'), function ($query) use ($request) {
                $query->where('miti', '>=', $request->miti_from);
            })
            ->when($request->filled('miti_upto'), function ($query) use ($request) {
                $query->where('miti', '<=', $request->miti_upto);
            })
            ->when($request->filled('shift'), function ($query) use ($request) {
                $query->where('shift', $request->shift);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $info = $request->search;
                $query->where(function ($q) use ($info) {
                    $q->whereHas('customer', function ($customerQuery) use ($info) {
                        $customerQuery->where('name', 'like', "%{$info}%");
                    })
                        ->orWhere('description', 'like', "%{$info}%")
                        ->orWhere('status', 'like', "%{$info}%")
                        ->orWhere('shift', 'like', "%{$info}%")
                        ->orWhere('amount', 'like', "%{$info}%")
                        ->orWhere('invoice_no', 'like', "%{$info}%")
                        ->orWhere('return_amount', 'like', "%{$info}%");
                });
            });
        $summaryCredits = (clone $query)->get();

        $summary = [
            'total_records' => $summaryCredits->count(),
            'total_credit_amount' => round($summaryCredits->sum('amount'), 2),
            'returned_amount' => round($summaryCredits->sum('return_amount'), 2),
            'remaining_amount' => round(
                $summaryCredits->sum(fn($credit) => $credit->amount - $credit->return_amount),
                2
            ),
            'completed' => $summaryCredits->where('status', 'completed')->count(),
            'pending' => $summaryCredits->where('status', 'pending')->count(),
        ];

        $credits = $query
            ->orderBy('date', 'DESC')
            ->paginate($request->limit ?? $limit);

        return [
            'summary' => $summary,
            'data' => CreditResource::collection($credits),
            'links' => [
                'first' => $credits->url(1),
                'last' => $credits->url($credits->lastPage()),
                'prev' => $credits->previousPageUrl(),
                'next' => $credits->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $credits->currentPage(),
                'from' => $credits->firstItem(),
                'last_page' => $credits->lastPage(),
                'per_page' => $credits->perPage(),
                'to' => $credits->lastItem(),
                'total' => $credits->total(),
            ],
        ];
    }

    public function store($data)
    {
        try {
            $credit = $this->credit->create($data);
            if ($credit) {
                LedgerService::postCredit($credit);
            }
            return $credit;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $credit = $this->credit->find($id);
        if (!$credit) {
            return null;
        }
        return $resource ? new CreditResource($credit) : $credit;
    }

    public function update($id, $data)
    {
        try {
            $credit = $this->find($id);
            if (!$credit) {
                return false;
            }
            $updated = $credit->update($data);
            if ($updated) {
                LedgerService::postCredit($credit);
            }
            return $updated;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            DB::transaction(function () use ($id) {

                $credit = $this->find($id);

                if (!$credit) {
                    throw new \Exception('Credit not found');
                }

                LedgerService::deleteByReference(
                    'credit',
                    $credit->id
                );

                $credit->delete();
            });

            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }
}
