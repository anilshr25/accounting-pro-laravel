<?php

namespace App\Http\Requests\Tenant\BankAccount;

use Illuminate\Foundation\Http\FormRequest;

class BankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'account_type' => 'nullable|string|max:255',
            'balance' => 'nullable|numeric',
        ];
    }
}
