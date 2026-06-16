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
        $chequeQuery = $this->cheque
            ->when($request->filled('bank_account_id'), function ($query) use ($request) {
                $bankIds = is_array($request->bank_account_id)
                    ? $request->bank_account_id
                    : [$request->bank_account_id];

                $query->whereIn('bank_account_id', $bankIds);
            })
            ->when($request->filled('party_type'), function ($query) use ($request) {
                $query->where('party_type', $request->party_type);
            })
            ->when($request->filled('party_id'), function ($query) use ($request) {
                $query->where('party_id', $request->party_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->whereHasMorph(
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
                        $q->orWhere('amount', $search);
                    }
                });
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->filled('cheque_number'), function ($query) use ($request) {
                $query->where('cheque_number', 'like', "%{$request->cheque_number}%");
            })
            ->when($request->filled('amount'), function ($query) use ($request) {
                $query->where('amount', $request->amount);
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
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            });
        $totalAmount = (clone $chequeQuery)->sum('amount');

        $cheques = $chequeQuery->orderBy('date', 'DESC')
            ->paginate($request->limit ?? $limit);
        return [
            'data' => ChequeResource::collection($cheques),
            'total_amount' => $totalAmount,
            'links' => $cheques->links(),
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
