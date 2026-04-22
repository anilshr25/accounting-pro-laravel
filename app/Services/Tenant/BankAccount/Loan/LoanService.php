<?php

namespace App\Services\Tenant\BankAccount\Loan;

use App\Models\Tenant\BankAccount\Loan\Loan;
use App\Http\Resources\Tenant\BankAccount\Loan\LoanResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

class LoanService
{
    protected $loan;

    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    public function payLoan($id, $amount)
    {
        DB::beginTransaction();

        try {
            $loan = $this->loan->find($id);

            if (!$loan) return false;

            if ($loan->status === 'closed') {
                return [
                    'error' => true,
                    'message' => 'Loan is already closed. Payment not allowed.'
                ];
            }

            if ($amount > $loan->remaining_amount) {
                return [
                    'error' => true,
                    'message' => 'Payment exceeds remaining amount.'
                ];
            }

            $loan->paid_amount += $amount;
            $loan->remaining_amount -= $amount;
            $loan->last_paid_date = now();

            if ($loan->remaining_amount <= 0) {
                $loan->remaining_amount = 0;
                $loan->status = 'closed';
            } else {
                $loan->next_due_date = Carbon::parse($loan->next_due_date)
                    ->addMonthNoOverflow();

                $loan->current_month += 1;
            }

            $loan->save();

            DB::commit();

            return $loan;
        } catch (\Exception $ex) {
            DB::rollBack();
            return false;
        }
    }

    private function fields()
    {
        return [
            'id',
            'bank_account_id',
            'loan_number',
            'principal_amount',
            'premium_rate',
            'base_rate',
            'duration_months',
            'loan_type',
            'emi_amount',
            'total_amount',
            'remaining_amount',
            'paid_amount',
            'current_month',
            'next_due_date',
            'last_paid_date',
            'collateral',
            'repayment_schedule',
            'late_payment_charge',
            'start_date',
            'start_miti',
            'end_date',
            'end_miti',
            'status',
            'remarks'
        ];
    }

    private function calculateLoan($principal, $annualRate, $months)
    {
        $monthlyRate = ($annualRate / 12) / 100;

        if ($monthlyRate == 0) {
            $emi = $principal / $months;
        } else {
            $emi = ($principal * $monthlyRate * pow(1 + $monthlyRate, $months))
                / (pow(1 + $monthlyRate, $months) - 1);
        }

        $totalAmount = $emi * $months;
        $totalInterest = $totalAmount - $principal;

        return [
            'emi' => round($emi, 2),
            'total_amount' => round($totalAmount, 2),
            'total_interest' => round($totalInterest, 2),
        ];
    }

    public function paginate($request, $limit = 25)
    {
        $loan = $this->loan
            ->select($this->fields())
            ->when(
                $request->filled('loan_number'),
                fn($q) => $q->where('loan_number', $request->loan_number)
            )
            ->when(
                $request->filled('status'),
                fn($q) => $q->where('status', $request->status)
            )
            ->when(
                $request->filled('loan_type'),
                fn($q) => $q->where('loan_type', $request->loan_type)
            )
            ->latest()
            ->paginate($request->limit ?? $limit);

        return LoanResource::collection($loan);
    }

    public function store($data)
    {
        DB::beginTransaction();

        try {
            $calc = $this->calculateLoan(
                $data['principal_amount'],
                $data['premium_rate'],
                $data['duration_months']
            );

            $data['emi_amount'] = $calc['emi'];
            $data['total_amount'] = $calc['total_amount'];
            $data['remaining_amount'] = $calc['total_amount'];

            $data['paid_amount'] = 0;
            $data['current_month'] = 1;

            $data['next_due_date'] = isset($data['start_date'])
                ? Carbon::parse($data['start_date'])->addMonth()
                : null;
            $loan = $this->loan->create($data);

            DB::commit();

            return $loan;
        } catch (QueryException $ex) {
            DB::rollBack();
            if ($ex->errorInfo[1] == 1062) {
                return [
                    'error' => true,
                    'message' => 'Loan number already exists. Please use a different loan number.'
                ];
            }

            return [
                'error' => true,
                'message' => 'Database error occurred.'
            ];
        } catch (\Exception $ex) {
            DB::rollBack();

            return [
                'error' => true,
                'message' => 'Something went wrong.'
            ];
        }
    }

    public function find($id, $resource = false)
    {
        $loan = $this->loan
            ->select($this->fields())
            ->find($id);

        if (!$loan) {
            return null;
        }

        return $resource ? new LoanResource($loan) : $loan;
    }

    public function show($id)
    {
        $loan = $this->find($id);

        return $loan ? new LoanResource($loan) : null;
    }

    public function update($id, $data)
    {
        DB::beginTransaction();

        try {
            $loan = $this->loan->find($id);

            if (!$loan) {
                return null;
            }

            $principal = $data['principal_amount'] ?? $loan->principal_amount;
            $rate = $data['premium_rate'] ?? $loan->premium_rate;
            $months = $data['duration_months'] ?? $loan->duration_months;

            if (
                isset($data['principal_amount']) ||
                isset($data['premium_rate']) ||
                isset($data['duration_months'])
            ) {
                if ($months <= 0) {
                    throw new \Exception('Duration must be greater than 0');
                }

                $calc = $this->calculateLoan($principal, $rate, $months);

                $data['emi_amount'] = $calc['emi'];
                $data['total_amount'] = $calc['total_amount'];

                if ($loan->remaining_amount == $loan->total_amount) {
                    $data['remaining_amount'] = $calc['total_amount'];
                }
            }

            $data = array_filter($data, function ($value) {
                return !is_null($value);
            });

            $loan->update($data);

            DB::commit();

            return $loan->fresh();
        } catch (\Exception $ex) {
            DB::rollBack();
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $loan = $this->loan->find($id);

            if (!$loan) {
                return false;
            }

            $loan->delete();

            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }
}
