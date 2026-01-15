<?php

namespace App\Services\Tenant\Ledger;

use App\Http\Resources\Tenant\Ledger\LedgerResource;
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
            ->when(
                $request->filled('date'),
                fn($q) =>
                $q->whereDate('date', $request->date)
            )
            ->when(
                $request->filled('party_type'),
                fn($q) =>
                $q->where('party_type', $request->party_type)
            )
            ->when(
                $request->filled('party_id'),
                fn($q) =>
                $q->where('party_id', $request->party_id)
            )
            ->when(
                $request->filled('party_info'),
                fn($q) =>
                $q->whereHasMorph('party', ['supplier', 'customer'], function ($query, $type) use ($request) {
                    $info = $request->party_info;
                    $query->where('name', 'like', "%{$info}%")
                        ->orWhere('email', 'like', "%{$info}%");
                    if ($type === 'supplier') {
                        $query->orWhere('pan', 'like', "%{$info}%");
                    }
                })
            )
            ->when(
                $request->filled('reference_type'),
                fn($q) =>
                $q->where('reference_type', $request->reference_type)
            )
            ->when(
                $request->filled('reference_id'),
                fn($q) =>
                $q->where('reference_id', $request->reference_id)
            )
            ->orderBy('date')
            ->orderBy('id')
            ->paginate($request->integer('limit', $limit));

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
                ->value('balance');

            $debit = 0;
            $credit = 0;

            // Accounting logic
            if ($payment->party_type === 'customer') {
                $debit = $payment->amount;
                $openingBalance = $payment->party?->credit_balance ?? 0;
                $baseBalance = $lastBalance ?? $openingBalance;
                $newBalance = $baseBalance - $debit;
            } elseif ($payment->party_type === 'supplier') {
                $credit = $payment->amount;
                $openingBalance = $payment->party?->opening_balance ?? 0;
                $baseBalance = $lastBalance ?? $openingBalance;
                $newBalance = $baseBalance - $credit;
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
                ->value('balance');

            $debit = 0;
            $credit = 0;

            // Accounting logic
            if ($cheque->party_type === 'customer') {
                $debit = $cheque->amount;
                $openingBalance = $cheque->party?->credit_balance ?? 0;
                $baseBalance = $lastBalance ?? $openingBalance;
                $newBalance = $baseBalance - $debit;
            } elseif ($cheque->party_type === 'supplier') {
                $credit = $cheque->amount;
                $openingBalance = $cheque->party?->opening_balance ?? 0;
                $baseBalance = $lastBalance ?? $openingBalance;
                $newBalance = $baseBalance - $credit;
            } else {
                throw new \Exception('Unsupported party type');
            }

            // Create ledger entry
            $ledger = new Ledger([
                'date' => $cheque->date,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance,
                'remarks' => 'Cheque',
            ]);

            $ledger->party()->associate($cheque->party);
            $ledger->reference()->associate($cheque);
            $ledger->save();

            $cheque->update(['is_posted' => true]);
        });
    }

    public static function syncChequeLedger($cheque)
    {
        DB::transaction(function () use ($cheque) {
            $partyType = $cheque->party_type;
            $partyId = $cheque->party_id;

            if (!$partyType || !$partyId) {
                return;
            }

            $existing = Ledger::where('reference_type', 'cheque')
                ->where('reference_id', $cheque->id)
                ->first();

            $lastBalanceQuery = Ledger::where('party_type', $partyType)
                ->where('party_id', $partyId);

            if ($existing) {
                $lastBalanceQuery->where('id', '!=', $existing->id);
            }

            $lastBalance = $lastBalanceQuery->latest('date')->latest('id')->value('balance');

            $debit = 0;
            $credit = 0;

            if ($partyType === 'customer') {
                $debit = $cheque->amount ?? 0;
                $openingBalance = $cheque->party?->credit_balance ?? 0;
                $baseBalance = $lastBalance ?? $openingBalance;
                $newBalance = $baseBalance - $debit;
            } elseif ($partyType === 'supplier') {
                $credit = $cheque->amount ?? 0;
                $openingBalance = $cheque->party?->opening_balance ?? 0;
                $baseBalance = $lastBalance ?? $openingBalance;
                $newBalance = $baseBalance + $credit;
            } else {
                return;
            }

            $data = [
                'date' => $cheque->date,
                'party_type' => $partyType,
                'party_id' => $partyId,
                'debit' => $debit,
                'credit' => $credit,
                'reference_type' => 'cheque',
                'reference_id' => $cheque->id,
                'remarks' => 'Cheque',
                'balance' => $newBalance,
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                Ledger::create($data);
            }
        });
    }

    public static function postPurchaseOrder($purchaseOrder)
    {
        DB::transaction(function () use ($purchaseOrder) {
            $partyType = 'supplier';
            $partyId = $purchaseOrder->supplier_id;

            $existing = Ledger::where('reference_type', 'purchase_order')
                ->where('reference_id', $purchaseOrder->id)
                ->first();

            $lastBalanceQuery = Ledger::where('party_type', $partyType)
                ->where('party_id', $partyId);

            if ($existing) {
                $lastBalanceQuery->where('id', '!=', $existing->id);
            }

            $lastBalance = $lastBalanceQuery->latest('date')->latest('id')->value('balance');
            $openingBalance = $purchaseOrder->supplier?->opening_balance ?? 0;
            $baseBalance = $lastBalance ?? $openingBalance;
            $debit = $purchaseOrder->total ?? 0;
            $newBalance = $baseBalance + $debit;

            $data = [
                'date' => $purchaseOrder->order_date ?? $purchaseOrder->received_date,
                'party_type' => $partyType,
                'party_id' => $partyId,
                'debit' => $debit,
                'credit' => 0,
                'reference_type' => 'purchase_order',
                'reference_id' => $purchaseOrder->id,
                'remarks' => 'Purchase Order',
                'balance' => $newBalance,
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                Ledger::create($data);
            }
        });
    }

    public static function postCredit($credit)
    {
        DB::transaction(function () use ($credit) {
            $partyType = 'customer';
            $partyId = $credit->customer_id;

            if (!$partyId) {
                return;
            }

            $existing = Ledger::where('reference_type', 'credit')
                ->where('reference_id', $credit->id)
                ->first();

            $lastBalanceQuery = Ledger::where('party_type', $partyType)
                ->where('party_id', $partyId);

            if ($existing) {
                $lastBalanceQuery->where('id', '!=', $existing->id);
            }

            $lastBalance = $lastBalanceQuery->latest('date')->latest('id')->value('balance');
            $openingBalance = $credit->customer?->credit_balance ?? 0;
            $baseBalance = $lastBalance ?? $openingBalance;
            $creditAmount = $credit->amount ?? 0;
            $newBalance = $baseBalance + $creditAmount;

            $data = [
                'date' => $credit->date,
                'party_type' => $partyType,
                'party_id' => $partyId,
                'debit' => 0,
                'credit' => $creditAmount,
                'reference_type' => 'credit',
                'reference_id' => $credit->id,
                'remarks' => 'Credit',
                'balance' => $newBalance,
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                Ledger::create($data);
            }
        });
    }
    public static function deleteCheque($chequeId)
    {
        DB::transaction(Ledger::query()
            ->where('reference_type', 'cheque')
            ->where('reference_id', $chequeId)
            ->delete(...));
    }
}
