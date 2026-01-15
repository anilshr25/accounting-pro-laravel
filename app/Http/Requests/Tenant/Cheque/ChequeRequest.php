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
            'type' => 'required|string|max:255',
            'cheque_number' => 'required|string|max:255',
            'party_id' => 'required|integer',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'miti' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
        ];
    }
}
