<?php

namespace App\Services\Tenant\Report;

use App\Models\Tenant\BankAccount\Loan\Loan;

class LoanReportService
{
    public function getReport($request)
    {
        $query = Loan::with([
            'bank_account:id,bank_name',
            'payments'
        ]);

        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->filled('loan_type')) {
            $query->where('loan_type', $request->loan_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->when($request->filled('from_date'), function ($q) use ($request) {
            $q->whereDate('start_date', '>=', $request->from_date);
        });

        $query->when($request->filled('to_date'), function ($q) use ($request) {
            $q->whereDate('start_date', '<=', $request->to_date);
        });

        $loans = $query->get();

        $summary = [
            'total_loans' => $loans->count(),
            'active_loans' => $loans->where('status', 'active')->count(),
            'closed_loans' => $loans->where('status', 'closed')->count(),
            'principal_amount' => round($loans->sum('principal_amount'), 2),
            'total_amount' => round($loans->sum('total_amount'), 2),
            'remaining_amount' => round($loans->sum('remaining_amount'), 2),
            'paid_amount' => round(
                $loans->sum(function ($loan) {
                    return $loan->payments->sum('amount');
                }),
                2
            ),
            'monthly_emi' => round($loans->sum('emi_amount'), 2),
        ];

        $data = $loans->map(function ($loan) {

            $paidAmount = $loan->payments->sum('amount');

            $paidInstallments = $loan->payments
                ->whereNotNull('paid_date')
                ->count();

            $remainingInstallments = $loan->payments
                ->whereNull('paid_date')
                ->count();

            return [
                'id' => $loan->id,
                'loan_number' => $loan->loan_number,
                'bank_account' => optional($loan->bank_account)->bank_name,

                'loan_type' => $loan->loan_type,
                'status' => $loan->status,

                'principal_amount' => $loan->principal_amount,
                'base_rate' => $loan->base_rate,
                'premium_rate' => $loan->premium_rate,
                'interest_rate' => $loan->base_rate + $loan->premium_rate,

                'emi_amount' => $loan->emi_amount,
                'total_amount' => $loan->total_amount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $loan->remaining_amount,

                'total_installments' => $loan->payments->count(),
                'paid_installments' => $paidInstallments,
                'remaining_installments' => $remainingInstallments,

                'start_date' => $loan->start_date?->format('Y-m-d'),
                'end_date' => $loan->end_date?->format('Y-m-d'),
                'start_miti' => $loan->start_miti,
                'end_miti' => $loan->end_miti,

                'collateral' => $loan->collateral,
                'remarks' => $loan->remarks,
            ];
        });

        return [
            'data' => $data,
            'summary' => $summary,
        ];
    }
}
