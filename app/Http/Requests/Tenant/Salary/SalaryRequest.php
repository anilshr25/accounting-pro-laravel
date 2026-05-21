<?php

namespace App\Http\Requests\Tenant\Salary;

use Illuminate\Foundation\Http\FormRequest;

class SalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|exists:employees,id',
            'month' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'status' => 'nullable|in:pending,paid',
        ];
    }
}
