<?php

namespace App\Http\Requests\Tenant\Balance;

use Illuminate\Foundation\Http\FormRequest;

class BalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'nullable|date',
            'opening_balance' => 'nullable|numeric',
            'closing_balance' => 'nullable|numeric',
            'shift' => 'nullable|string|max:255',
        ];
    }
}
