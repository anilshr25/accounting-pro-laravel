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
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);
        $id = $this->route('id');

        return [

            'bank_account_id' => $isUpdate
                ? 'sometimes|exists:bank_accounts,id'
                : 'required|exists:bank_accounts,id',
            'loan_number' => $isUpdate
                ? 'sometimes|string|max:255|unique:loans,loan_number,' . $id
                : 'required|string|max:255|unique:loans,loan_number',
            'principal_amount' => $isUpdate
                ? 'sometimes|numeric|min:1'
                : 'required|numeric|min:1',
            'base_rate' => $isUpdate
                ? 'sometimes|numeric|min:0'
                : 'required|numeric|min:0',
            'premium_rate' => $isUpdate
                ? 'sometimes|numeric|min:0'
                : 'required|numeric|min:0',
            'duration' => $isUpdate
                ? 'sometimes|integer|min:1'
                : 'required|integer|min:1',
            'payment_type' => $isUpdate
                ? 'sometimes|in:monthly,quarterly,yearly'
                : 'required|in:monthly,quarterly,yearly',
            'loan_type' => $isUpdate
                ? 'sometimes|in:od,home,business,term'
                : 'required|in:od,home,business,term',
            'start_date' => $isUpdate
                ? 'sometimes|date'
                : 'required|date',
            'collateral' => 'nullable|string|max:255',
            'start_miti' => 'nullable|string|max:255',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'end_miti' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,closed',
            'remarks' => 'nullable|string',
        ];
    }
}
