<?php

namespace App\Http\Requests\Tenant\Cheque;

use Illuminate\Foundation\Http\FormRequest;

class ChequeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_account_id' => 'nullable|integer',
            'supplier_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer',
            'type' => 'nullable|string|max:255',
            'cheque_number' => 'nullable|string|max:255',
            'pay_to' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'date' => 'nullable|date',
            'miti' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
        ];
    }
}
