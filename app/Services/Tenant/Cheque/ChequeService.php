<?php

namespace App\Services\Tenant\Cheque;

use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Cheque\Cheque;
use App\Services\Tenant\Ledger\LedgerService;
use App\Http\Resources\Tenant\Cheque\ChequeResource;
use Carbon\Carbon;

class ChequeService
{
    protected $cheque;
    public function __construct(Cheque $cheque)
    {
        $this->cheque = $cheque;
    }

    public function paginate($request, $limit = 25)
    {
        $fiscalYear = $request->input('fiscal_year');

        [$startYear] = explode('/', $fiscalYear);

        $mitiFrom = $startYear . '-04-01';
        $mitiUpto = ($startYear + 1) . '-03-31';

        $bankAccountIds = [];
        if ($request->filled('bank_account_ids')) {
            $bankAccountIds = json_decode($request->bank_account_ids, true);
            if (!is_array($bankAccountIds)) {
                $bankAccountIds = [];
            }
        }
        $query = $this->cheque
            ->whereBetween('miti', [$mitiFrom, $mitiUpto])
            ->when(!empty($bankAccountIds), function ($q) use ($bankAccountIds) {
                $q->whereIn('bank_account_id', $bankAccountIds);
            })
            ->when($request->filled('party_type'), fn($q) => $q->where('party_type', $request->party_type))
            ->when($request->filled('party_id'), fn($q) => $q->where('party_id', $request->party_id))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('cheque_number'), fn($q) => $q->where('cheque_number', 'like', "%{$request->cheque_number}%"))
            ->when($request->filled('amount'), fn($q) => $q->where('amount', $request->amount))
            ->when(
                $request->filled('date_from'),
                fn($q) => $q->whereDate('date', '>=', $request->date_from)
            )
            ->when(
                $request->filled('date_upto'),
                fn($q) => $q->whereDate('date', '<=', $request->date_upto)
            )
            ->when(
                $request->filled('miti_from'),
                fn($q) => $q->where('miti', '>=', $request->miti_from)
            )
            ->when(
                $request->filled('miti_upto'),
                fn($q) => $q->where('miti', '<=', $request->miti_upto)
            )
            ->when(
                !$request->filled('date_from') &&
                    !$request->filled('date_upto') &&
                    !$request->filled('miti_from') &&
                    !$request->filled('miti_upto'),
                fn($q) => $q->whereDate('date', '<=', Carbon::today())
            )
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($query) use ($search) {
                    $query->whereHasMorph(
                        'party',
                        ['supplier', 'customer'],
                        function ($partyQuery) use ($search) {
                            $partyQuery->where('name', 'like', "%{$search}%");
                        }
                    )
                        ->orWhere('cheque_number', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $query->orWhere('amount', $search);
                    }
                });
            });

        $summaryQuery = clone $query;

        $bankAccountIds = $request->filled('bank_account_id')
            ? (
                is_array($request->bank_account_id)
                ? $request->bank_account_id
                : [$request->bank_account_id]
            )
            : [];

        $summaryCheques = $summaryQuery->get();

        $summary = [
            'cheque_count' => $summaryCheques->count(),
            'cleared_amount' => round(
                $summaryCheques->where('status', 'cleared')->sum('amount'),
                2
            ),
            'pending_amount' => round(
                $summaryCheques->where('status', 'pending')->sum('amount'),
                2
            ),
            'cancelled_amount' => round(
                $summaryCheques->where('status', 'cancelled')->sum('amount'),
                2
            ),
            'total_amount' => round(
                $summaryCheques->sum('amount'),
                2
            ),
        ];

        if ($request->status === 'pending') {
            $query->orderBy('date', 'ASC');
        } else {
            $query->orderBy('date', 'DESC');
        }

        $cheques = $query->paginate($request->limit ?? $limit);

        return [
            'summary' => $summary,
            'data' => ChequeResource::collection($cheques),
            'links' => [
                'first' => $cheques->url(1),
                'last' => $cheques->url($cheques->lastPage()),
                'prev' => $cheques->previousPageUrl(),
                'next' => $cheques->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $cheques->currentPage(),
                'from' => $cheques->firstItem(),
                'last_page' => $cheques->lastPage(),
                'per_page' => $cheques->perPage(),
                'to' => $cheques->lastItem(),
                'total' => $cheques->total(),
            ],
        ];
    }

    public function store($data, $user)
    {
        try {
            if (!empty($user)) {
                return DB::transaction(function () use ($data, $user) {
                    $cheque = new Cheque($data);

                    $cheque->party()->associate($user);

                    $cheque->save();

                    LedgerService::postCheaque($cheque);

                    return $cheque;
                });
            } else {
                return $this->cheque->create($data);
            }
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $cheque = $this->cheque->find($id);
        if (!$cheque) {
            return null;
        }
        return $resource ? new ChequeResource($cheque) : $cheque;
    }

    public function update($id, $data)
    {
        try {
            $cheque = $this->find($id);
            if (!$cheque) {
                return false;
            }
            $updated = $cheque->update($data);
            if ($updated) {
                LedgerService::syncChequeLedger($cheque);
            }
            return $updated;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function chequeClear($id, $data)
    {
        try {
            $cheque = $this->find($id);
            if (!$cheque) {
                return false;
            }
            return $cheque->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function chequeCancel($id, $data)
    {
        try {
            $cheque = $this->find($id);
            if (!$cheque) {
                return false;
            }
            LedgerService::deleteCheque($cheque->id);

            return $cheque->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            DB::transaction(function () use ($id) {

                $cheque = $this->find($id);

                if (!$cheque) {
                    throw new \Exception('Cheque not found');
                }

                LedgerService::deleteByReference(
                    'cheque',
                    $cheque->id
                );

                $cheque->delete();
            });

            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }
}
