<?php

namespace App\Http\Requests\Tenant\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'opening_balance' => 'required|numeric',
            'pan' => 'required|string|max:255',
        ];
    }
}
