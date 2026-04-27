<?php

namespace App\Http\Requests\Tenant\BankAccount\Loan\Payment;

use Illuminate\Foundation\Http\FormRequest;

class LoanPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);

        return [

            'loan_id' => $isUpdate
                ? 'sometimes|exists:loans,id'
                : 'required|exists:loans,id',

            'amount' => $isUpdate
                ? 'sometimes|numeric|min:0'
                : 'required|numeric|min:0',

            'due_date' => $isUpdate
                ? 'sometimes|date'
                : 'nullable|date',

            'paid_date' => 'nullable|date',

            'late_payment_charge' => 'nullable|numeric|min:0',

            'status' => $isUpdate
                ? 'sometimes|in:pending,paid,overdue'
                : 'nullable|in:pending,paid,overdue',
        ];
    }
}
