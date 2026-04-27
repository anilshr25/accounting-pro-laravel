<?php

namespace App\Http\Resources\Tenant\BankAccount\Loan\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanPaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'loan_id' => $this->loan_id,
            'amount' => $this->amount,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'paid_date' => $this->paid_date?->format('Y-m-d'),
            'late_payment_charge' => $this->late_payment_charge,
            'status' => $this->status,
        ];
    }
}
