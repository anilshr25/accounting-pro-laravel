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

    public static function deleteByReference(string $referenceType, int $referenceId): void
    {
        Ledger::withTrashed()
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->delete();
    }


    public function paginate($request, $limit = 25)
    {
        $ledgers = $this->ledger
            ->with(['party', 'reference'])
            ->whereNull('deleted_at')

            ->when($request->filled('search'), function ($query) use ($request) {

                $search = $request->search;

                $query->where(function ($q) use ($search) {

                    $q->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhere('reference_type', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $q->orWhere('debit', $search)
                            ->orWhere('credit', $search)
                            ->orWhere('balance', $search);
                    }

                    $q->orWhereHasMorph(
                        'party',
                        ['supplier', 'customer'],
                        function ($party) use ($search) {
                            $party->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        }
                    );
                });
            })
            ->when(
                $request->filled('date'),
                fn($q) =>
                $q->whereDate('date', $request->date)
            )
            ->when(
                $request->filled('miti'),
                fn($q) =>
                $q->whereDate('miti', $request->miti)
            )
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
                $request->filled('remarks'),
                fn($q) =>
                $q->where('remarks', 'like', "%{$request->remarks}%")
            )
            ->when(
                $request->filled('invoice_number'),
                fn($q) =>
                $q->where('invoice_number', $request->invoice_number)
            )
            ->when(
                $request->filled('amount'),
                fn($q) =>
                $q->where(function ($query) use ($request) {
                    $amount = $request->amount;
                    $query->where('debit', $amount)
                        ->orWhere('credit', $amount);
                })
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
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($request->integer('limit', $limit));

        if ($request->filled('fiscal_year')) {
            $ledgers->setCollection(
                $ledgers->getCollection()->filter(function ($ledger) use ($request) {
                    return $ledger->fiscal_year === $request->fiscal_year;
                })->values()
            );
        }

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
        return $this->ledger->withoutTrashed()->find($id);
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

    public function adjustBalances(string $partyType, int $partyId, ?string $dateFrom = null)
    {
        try {
            $openingBalance = $this->getOpeningBalance($partyType, $partyId);
            $query = Ledger::query()
                ->where('party_type', $partyType)
                ->where('party_id', $partyId)
                ->orderBy('date')
                ->orderBy('id');

            if ($dateFrom) {
                $query->whereDate('date', '>=', $dateFrom);
            }

            $ledgers = $query->get();

            if ($ledgers->isEmpty()) {
                return 0;
            }

            $previousBalance = null;
            if ($dateFrom) {
                $previousBalance = Ledger::query()
                    ->where('party_type', $partyType)
                    ->where('party_id', $partyId)
                    ->whereDate('date', '<', $dateFrom)
                    ->orderBy('date', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->value('balance');
            }

            $currentBalance = $previousBalance ?? $openingBalance;
            $first = $previousBalance === null;
            $useOpening = $previousBalance === null && $openingBalance != 0;
            $updated = 0;

            foreach ($ledgers as $ledger) {
                $debit = (float) ($ledger->debit ?? 0);
                $credit = (float) ($ledger->credit ?? 0);

                if ($first && $useOpening) {
                    $currentBalance = $this->calculateBalance($partyType, $openingBalance, $debit, $credit);
                    $first = false;
                } else {
                    $currentBalance = $this->calculateBalance($partyType, $currentBalance, $debit, $credit);
                }
                $ledger->update(['balance' => $currentBalance]);
                $updated++;
            }

            return $updated;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public static function postPayment($payment)
    {
        DB::transaction(function () use ($payment) {

            $ledger = Ledger::where('reference_type', 'payment')
                ->where('reference_id', $payment->id)
                ->first();

            $debit = 0;
            $credit = 0;

            if ($payment->party_type === 'customer') {
                $debit = $payment->amount;
            } elseif ($payment->party_type === 'supplier') {
                $credit = $payment->amount;
            }

            $lastBalance = Ledger::where('party_type', $payment->party_type)
                ->where('party_id', $payment->party_id)
                ->where('id', '!=', optional($ledger)->id)
                ->latest('date')
                ->latest('id')
                ->value('balance');

            $baseBalance = $lastBalance ?? ($payment->party?->credit_balance ?? 0);

            $newBalance = $payment->party_type === 'customer'
                ? $baseBalance - $debit
                : $baseBalance - $credit;

            $data = [
                'date' => $payment->date,
                'party_type' => $payment->party_type,
                'party_id' => $payment->party_id,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance,
                'remarks' => 'Payment',
            ];

            if ($ledger) {
                $ledger->update($data);
            } else {
                $ledger = new Ledger($data);
                $ledger->reference()->associate($payment);
                $ledger->save();
            }

            self::recalculateLedger(
                $payment->party_type,
                $payment->party_id,
                $payment->date
            );

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

            self::recalculateLedger(
                $cheque->party_type,
                $cheque->party_id,
                $cheque->date
            );

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

            $existing = Ledger::withTrashed()
                ->where('reference_type', 'cheque')
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
                $existing = Ledger::create($data);
            }

            self::recalculateLedger(
                $partyType,
                $partyId,
                $data['date']
            );
        });
    }

    public static function postPurchaseOrder($purchaseOrder)
    {
        DB::transaction(function () use ($purchaseOrder) {
            $partyType = 'supplier';
            $partyId = $purchaseOrder->supplier_id;

            $existing = Ledger::withTrashed()
                ->where('reference_type', 'purchase_order')
                ->where('reference_id', $purchaseOrder->id)
                ->first();

            if ($existing && $existing->trashed()) {
                return;
            }

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
                $existing = Ledger::create($data);
            }

            self::recalculateLedger(
                $partyType,
                $partyId,
                $data['date']
            );
        });
    }

    public static function postPurchaseReturn($purchaseReturn)
    {
        DB::transaction(function () use ($purchaseReturn) {

            $purchaseReturn->load('supplier');

            $supplier = $purchaseReturn->supplier;

            if (!$supplier) {
                throw new \Exception('Supplier not found for Purchase Return');
            }

            $partyType = 'supplier';
            $partyId   = $supplier->id;

            $existing = Ledger::withTrashed()
                ->where('reference_type', 'purchase_return')
                ->where('reference_id', $purchaseReturn->id)
                ->first();

            if ($existing && $existing->trashed()) {
                return;
            }

            $lastBalanceQuery = Ledger::where('party_type', $partyType)
                ->where('party_id', $partyId);

            if ($existing) {
                $lastBalanceQuery->where('id', '!=', $existing->id);
            }

            $lastBalance = $lastBalanceQuery
                ->latest('date')
                ->latest('id')
                ->value('balance');

            $openingBalance = $supplier->opening_balance ?? 0;
            $baseBalance    = $lastBalance ?? $openingBalance;

            $credit    = $purchaseReturn->total ?? 0;
            $newBalance = $baseBalance - $credit;

            $data = [
                'date'           => $purchaseReturn->return_date,
                'party_type'     => $partyType,
                'party_id'       => $partyId,
                'debit'          => 0,
                'credit'         => $credit,
                'reference_type' => 'purchase_return',
                'reference_id'   => $purchaseReturn->id,
                'remarks'        => 'Purchase Return',
                'balance'        => $newBalance,
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                $existing = Ledger::create($data);
            }

            self::recalculateLedger(
                $partyType,
                $partyId,
                $data['date']
            );
        });
    }


    public static function postInvoiceReturn($invoiceReturn)
    {
        DB::transaction(function () use ($invoiceReturn) {

            $invoiceReturn->load('customer');

            $customer = $invoiceReturn->customer;

            if (!$customer) {
                throw new \Exception('Customer not found for Invoice');
            }

            $partyType = 'customer';
            $partyId   = $customer->id;

            $existing = Ledger::withTrashed()
                ->where('reference_type', 'invoice_return')
                ->where('reference_id', $invoiceReturn->id)
                ->first();

            if ($existing && $existing->trashed()) {
                return;
            }

            $lastBalanceQuery = Ledger::where('party_type', $partyType)
                ->where('party_id', $partyId);

            if ($existing) {
                $lastBalanceQuery->where('id', '!=', $existing->id);
            }

            $lastBalance = $lastBalanceQuery
                ->latest('date')
                ->latest('id')
                ->value('balance');

            $openingBalance = $customer->credit_balance ?? 0;
            $baseBalance    = $lastBalance ?? $openingBalance;

            $debit      = $invoiceReturn->total ?? 0;
            $newBalance = $baseBalance - $debit;

            $data = [
                'date'           => $invoiceReturn->return_date,
                'party_type'     => $partyType,
                'party_id'       => $partyId,
                'debit'          => $debit,
                'credit'         => 0,
                'reference_type' => 'invoice_return',
                'reference_id'   => $invoiceReturn->id,
                'remarks'        => 'Invoice Return',
                'balance'        => $newBalance,
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                $existing = Ledger::create($data);
            }

            self::recalculateLedger(
                $partyType,
                $partyId,
                $data['date']
            );
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

            $existing = Ledger::withTrashed()
                ->where('reference_type', 'credit')
                ->where('reference_id', $credit->id)
                ->first();

            if ($existing && $existing->trashed()) {
                return;
            }

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
                $existing = Ledger::create($data);
            }

            self::recalculateLedger(
                $partyType,
                $partyId,
                $data['date']
            );
        });
    }

    public static function deleteCheque($chequeId)
    {
        DB::transaction(function () use ($chequeId) {

            $ledger = Ledger::where('reference_type', 'cheque')
                ->where('reference_id', $chequeId)
                ->first();

            if (!$ledger) {
                return;
            }

            $partyType = $ledger->party_type;
            $partyId   = $ledger->party_id;
            $date      = $ledger->date;

            $ledger->delete();

            self::recalculateLedger(
                $partyType,
                $partyId,
                $date
            );
        });
    }

    protected function getOpeningBalance(string $partyType, int $partyId): float
    {
        $ledger = new Ledger([
            'party_type' => $partyType,
            'party_id' => $partyId,
        ]);
        $party = $ledger->party;

        if (!$party) {
            return 0.0;
        }

        if ($partyType === 'supplier') {
            return (float) ($party->opening_balance ?? 0);
        }
        if ($partyType === 'customer') {
            return (float) ($party->credit_balance ?? 0);
        }
        return 0.0;
    }

    protected function calculateBalance(string $partyType, float $base, float $debit, float $credit): float
    {
        if ($partyType === 'supplier') {
            return $debit != 0 ? $base + $debit : $base - $credit;
        }

        if ($partyType === 'customer') {
            return $credit != 0 ? $base + $credit : $base - $debit;
        }

        return $base + ($credit - $debit);
    }

    public function getLedger($request)
    {
        return $this->ledger
            ->with(['party', 'reference'])
            ->whereNull('deleted_at')
            ->where('party_type', $request->party_type)
            ->where('party_id', $request->party_id)
            ->when(
                !empty($request->date_from),
                fn($q) => $q->whereDate('date', '>=', $request->date_from)
            )
            ->when(
                !empty($request->date_to),
                fn($q) => $q->whereDate('date', '<=', $request->date_to)
            )
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
    }

    protected static function recalculateLedger(
        string $partyType,
        int $partyId,
        string $date
    ): void {
        app(self::class)->adjustBalances(
            $partyType,
            $partyId,
            $date
        );
    }
}
