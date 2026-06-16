<?php

namespace App\Http\Requests\Tenant\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class ExpensesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_type' => 'required|in:utilities,insurance,rent,salary,maintenance,supplies,travel,marketing,other',
            'title' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'expense_miti' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0|max:99999.99',
            'description' => 'nullable|string',
            'payment_method' => 'required|in:cash,fonepay',
            'status' => 'required|in:paid,cancelled',
        ];
    }

    public function messages(): array
    {
        return [
            'expense_type.required' => 'Expense type is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'payment_method.in' => 'Invalid payment method selected.',
        ];
    }
}
