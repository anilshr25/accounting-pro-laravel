<?php

namespace App\Services\Tenant\BankAccount\Loan\Payment;

use App\Models\Tenant\BankAccount\Loan\Loan;
use App\Models\Tenant\BankAccount\Loan\Payment\LoanPayment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanPaymentService
{
    protected $model;

    public function __construct(LoanPayment $model)
    {
        $this->model = $model;
    }

    private function updateLoanRemainingAmount($loanId)
    {
        $loan = Loan::find($loanId);

        if (!$loan) return;

        $paidTotal = $this->model
            ->where('loan_id', $loanId)
            ->where('status', 'paid')
            ->sum('amount');

        $loan->remaining_amount = max(
            0,
            $loan->total_amount - $paidTotal
        );

        $loan->save();
    }

    public function paginate($request, $limit = 25)
    {
        return $this->model
            ->when($request->filled('loan_id'), fn($q) => $q->where('loan_id', $request->loan_id))
            ->latest()
            ->paginate($request->limit ?? $limit);
    }

    public function store($data)
    {
        DB::beginTransaction();

        try {
            if (empty($data['due_date'])) {

                $lastPayment = $this->model
                    ->where('loan_id', $data['loan_id'])
                    ->latest('due_date')
                    ->first();

                if ($lastPayment) {

                    $loan = $lastPayment->loan;

                    $monthsToAdd = match ($loan->payment_type) {
                        'monthly' => 1,
                        'quarterly' => 3,
                        'yearly' => 12,
                        default => 1,
                    };

                    $data['due_date'] = Carbon::parse($lastPayment->due_date)
                        ->addMonthsNoOverflow($monthsToAdd)
                        ->toDateString();
                } else {
                    throw new \Exception('Due date is required for first payment');
                }
            }

            if (!empty($data['paid_date'])) {
                $data['status'] = 'paid';
            } else {
                $today = Carbon::today();

                if ($today->gt(Carbon::parse($data['due_date']))) {
                    $data['status'] = 'overdue';
                } else {
                    $data['status'] = 'pending';
                }
            }

            $payment = $this->model->create($data);

            $this->updateLoanRemainingAmount($data['loan_id']);

            DB::commit();

            return $payment;
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                'error' => true,
                'message' => 'Failed to create payment',
            ];
        }
    }

    public function update($id, $data)
    {
        DB::beginTransaction();

        try {
            $payment = $this->model->find($id);

            if (!$payment) {
                return null;
            }

            $oldStatus = $payment->status;

            $payment->update($data);

            if (
                isset($data['status']) &&
                $data['status'] === 'paid' &&
                $oldStatus !== 'paid'
            ) {
                $this->updateLoanRemainingAmount($payment->loan_id);
            }

            DB::commit();

            return $payment->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                'error' => true,
                'message' => 'Failed to update payment',
            ];
        }
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $payment = $this->model->find($id);

            if (!$payment) {
                return false;
            }

            $loanId = $payment->loan_id;

            $payment->delete();

            $this->updateLoanRemainingAmount($loanId);

            DB::commit();

            return true;
        } catch (\Throwable $e) {
            DB::rollBack();

            return false;
        }
    }
}
