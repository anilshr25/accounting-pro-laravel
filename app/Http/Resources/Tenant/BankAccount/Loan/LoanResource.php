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
            'duration' => $this->duration_months,
            'payment_type' => $this->payment_type,
            'loan_type' => $this->loan_type,
            'emi_amount' => $this->emi_amount,
            'total_amount' => $this->total_amount,
            'remaining_amount' => $this->remaining_amount,
            'payment_type' => $this->payment_type,
            'collateral' => $this->collateral,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'start_miti' => $this->start_miti,
            'end_date' => $this->end_date?->format('Y-m-d'),
            'end_miti' => $this->end_miti,
            'status' => $this->status,
            'remarks' => $this->remarks,
        ];
    }
}
