<?php

namespace App\Http\Requests\Tenant\Ledger;

use Illuminate\Foundation\Http\FormRequest;

class LedgerAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'party_type' => 'required|in:supplier,customer',
            'party_id' => 'required|integer',
            'date_from' => 'nullable|date',
        ];
    }
}
