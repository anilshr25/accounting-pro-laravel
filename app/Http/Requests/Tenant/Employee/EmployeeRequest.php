<?php

namespace App\Http\Requests\Tenant\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('employees', 'email')->ignore($this->route('id')),
            ],
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'joining_date' => 'nullable|date',
            'pan_no' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('employees', 'pan_no')->ignore($this->route('id')),
            ],
            'license_no' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}
