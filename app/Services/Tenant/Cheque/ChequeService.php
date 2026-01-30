<?php

namespace App\Services\Tenant\Cheque;

use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Cheque\Cheque;
use App\Services\Tenant\Ledger\LedgerService;
use App\Http\Resources\Tenant\Cheque\ChequeResource;

class ChequeService
{
    protected $cheque;
    public function __construct(Cheque $cheque)
    {
        $this->cheque = $cheque;
    }
    public function paginate($request, $limit = 25)
    {
        $cheque = $this->cheque
            ->when($request->filled('bank_account_id'), function ($query) use ($request) {
                $query->where('bank_account_id', $request->bank_account_id);
            })
            ->when($request->filled('party_type'), function ($query) use ($request) {
                $query->where('party_type', $request->party_type);
            })
            ->when($request->filled('party_id'), function ($query) use ($request) {
                $query->where('party_id', $request->party_id);
            })
            ->when($request->filled('party_info'), function ($query) use ($request) {
                $info = $request->party_info;
                $query->whereHasMorph('party', ['supplier', 'customer'], function ($q, $type) use ($info) {
                    $q->where('name', 'like', "%{$info}%")
                        ->orWhere('email', 'like', "%{$info}%");
                    if ($type === 'supplier') {
                        $q->orWhere('pan', 'like', "%{$info}%");
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
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('date', $request->date);
            })
            ->when($request->filled('miti'), function ($query) use ($request) {
                $query->where('miti', 'like', "%{$request->miti}%");
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('bank_name'), function ($query) use ($request) {
                $query->where('bank_name', $request->bank_name);
            })
            ->orderBy('date', 'ASC')
            ->paginate($request->limit ?? $limit);
        return ChequeResource::collection($cheque);
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
