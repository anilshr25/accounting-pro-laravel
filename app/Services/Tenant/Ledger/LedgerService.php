<?php

namespace App\Services\Tenant\Ledger;

use App\Http\Resources\Tenant\Ledger\LedgerResource;
use App\Models\Tenant\Customer\Customer;
use App\Models\Tenant\Supplier\Supplier;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Ledger\Ledger;

class LedgerService
{
    protected $ledger;

    public function __construct(Ledger $ledger)
    {
        $this->ledger = $ledger;
    }

    public function paginate($request, $limit = 25)
    {
        $ledgers = $this->ledger
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('date', $request->date);
            })
            ->when($request->filled('party_type'), function ($query) use ($request) {
                $query->where('party_type', $request->party_type);
            })
            ->when($request->filled('party_id'), function ($query) use ($request) {
                $query->where('party_id', $request->party_id);
            })
            ->when($request->filled('debit'), function ($query) use ($request) {
                $query->where('debit', $request->debit);
            })
            ->when($request->filled('credit'), function ($query) use ($request) {
                $query->where('credit', $request->credit);
            })
            ->when($request->filled('reference_type'), function ($query) use ($request) {
                $query->where('reference_type', $request->reference_type);
            })
            ->when($request->filled('reference_id'), function ($query) use ($request) {
                $query->where('reference_id', $request->reference_id);
            })
            ->paginate($request->limit ?? $limit);
        return LedgerResource::collection($ledgers);
    }

    public function store($data)
    {
        try {
            return $this->ledger->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id)
    {
        return $this->ledger->find($id);
    }

    public function update($id, $data)
    {
        try {
            $ledger = $this->find($id);
            if (!$ledger) {
                return false;
            }
            return $ledger->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $ledger = $this->find($id);
            if (!$ledger) {
                return false;
            }
            return $ledger->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }

    public static function postPayment($payment)
    {
        DB::transaction(function () use ($payment) {
            // Prevent double posting
            if ($payment->is_posted) {
                throw new \Exception('Payment already posted to ledger.');
            }

            // Get last balance for this party
            $lastBalance = Ledger::where('party_type', $payment->party_type)
                ->where('party_id', $payment->party_id)
                ->latest('date')
                ->latest('id')
                ->value('balance') ?? 0;

            $debit = 0;
            $credit = 0;

            // Accounting logic
            if ($payment->party_type === 'customer') {
                $debit = $payment->amount;
                $newBalance = $lastBalance - $payment->amount;
            } elseif ($payment->party_type === 'supplier') {
                $credit = $payment->amount;
                $newBalance = $lastBalance - $payment->amount;
            } else {
                throw new \Exception('Unsupported party type');
            }

            // Create ledger entry
            $ledger = new Ledger([
                'date' => $payment->date,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance,
                'remarks' => 'Payment',
            ]);

            $ledger->party()->associate($payment->party);
            $ledger->reference()->associate($payment);
            $ledger->save();

            $payment->update(['is_posted' => true]);
        });
    }
    public static function postCheaque($cheque)
    {
        DB::transaction(function () use ($cheque) {

            // Prevent double posting
            if ($cheque->is_posted) {
                throw new \Exception('Cheaque already posted to ledger.');
            }

            // Get last balance for this party
            $lastBalance = Ledger::where('party_type', $cheque->party_type)
                ->where('party_id', $cheque->party_id)
                ->latest('date')
                ->latest('id')
                ->value('balance') ?? 0;

            $debit = 0;
            $credit = 0;

            // Accounting logic
            if ($cheque->party_type === 'customer') {
                $debit = $cheque->amount;
                $newBalance = $lastBalance - $cheque->amount;
            } elseif ($cheque->party_type === 'supplier') {
                $credit = $cheque->amount;
                $newBalance = $lastBalance - $cheque->amount;
            } else {
                throw new \Exception('Unsupported party type');
            }

            // Create ledger entry
            $ledger = new Ledger([
                'date' => $cheque->date,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance,
                'remarks' => 'Cheaque',
            ]);

            $ledger->party()->associate($cheque->party);
            $ledger->reference()->associate($cheque);
            $ledger->save();

            $cheque->update(['is_posted' => true]);
        });
    }
}
