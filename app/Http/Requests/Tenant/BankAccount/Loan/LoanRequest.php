<?php

namespace App\Http\Requests\Tenant\BankAccount\Loan;

use Illuminate\Foundation\Http\FormRequest;

class LoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->method() === 'PUT' || $this->method() === 'PATCH';

        return [
            'bank_account_id' => $isUpdate
                ? 'sometimes|exists:bank_accounts,id'
                : 'required|exists:bank_accounts,id',

            'loan_number' => 'nullable|string|max:255',
            'principal_amount' => 'nullable|numeric',
            'premium_rate' => 'nullable|numeric',
            'base_rate' => 'nullable|numeric',
            'duration_months' => 'nullable|integer|min:1',
            'loan_type' => 'nullable|string|max:255',
            'emi_amount' => 'nullable|numeric',
            'total_amount' => 'nullable|numeric',
            'remaining_amount' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric',
            'current_month' => 'nullable|integer|min:1',
            'collateral' => 'nullable|string|max:255',
            'repayment_schedule' => 'nullable|string|max:255',
            'late_payment_charge' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'start_miti' => 'nullable|string|max:255',
            'end_date' => 'nullable|date',
            'end_miti' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ];
    }
}
