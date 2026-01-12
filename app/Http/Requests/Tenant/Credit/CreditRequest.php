<?php

namespace App\Http\Requests\Tenant\Credit;

use Illuminate\Foundation\Http\FormRequest;

class CreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'return_amount' => 'nullable|numeric',
            'description' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'miti' => 'nullable|string|max:255',
            'shift' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'customer_id' => 'nullable|integer',
            'supplier_id' => 'nullable|integer',
        ];
    }
}
