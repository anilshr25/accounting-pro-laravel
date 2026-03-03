<?php

namespace App\Http\Requests\Tenant\Procurement\Item;

use Illuminate\Foundation\Http\FormRequest;

class ProcurementItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'nullable|integer',
            'procurement_id' => 'nullable|integer',
            'quantity' => 'nullable|numeric',
            'amount' => 'nullable|numeric',
        ];
    }
}
