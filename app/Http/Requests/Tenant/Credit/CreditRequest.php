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
            'invoice_no' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'return_amount' => 'nullable|numeric',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
            'miti' => 'required|string|max:255',
            'shift' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'customer_id' => 'required|integer',
            'supplier_id' => 'nullable|integer',
        ];
    }
}
