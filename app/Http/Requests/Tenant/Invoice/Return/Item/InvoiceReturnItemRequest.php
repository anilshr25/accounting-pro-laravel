<?php

namespace App\Http\Requests\Tenant\Invoice\Return\Item;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceReturnItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_return_id' => 'nullable|integer',
            'description' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric',
            'rate' => 'nullable|numeric',
            'amount' => 'nullable|numeric',
        ];
    }
}
