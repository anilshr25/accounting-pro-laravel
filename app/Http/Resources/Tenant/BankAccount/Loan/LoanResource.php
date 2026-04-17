<?php

namespace App\Http\Resources\Tenant\BankAccount\Loan;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'bank_account_id' => $this->bank_account_id,
            'loan_number' => $this->loan_number,
            'principal_amount' => $this->principal_amount,
            'premium_rate' => $this->premium_rate,
            'base_rate' => $this->base_rate,
            'duration_months' => $this->duration_months,
            'loan_type' => $this->loan_type,
            'emi_amount' => $this->emi_amount,
            'total_amount' => $this->total_amount,
            'remaining_amount' => $this->remaining_amount,
            'collateral' => $this->collateral,
            'repayment_schedule' => $this->repayment_schedule,
            'late_payment_charge' => $this->late_payment_charge,
            'start_date' => $this->start_date,
            'start_miti' => $this->start_miti,
            'end_date' => $this->end_date,
            'end_miti' => $this->end_miti,
            'status' => $this->status,
            'remarks' => $this->remarks,
        ];
    }
}
