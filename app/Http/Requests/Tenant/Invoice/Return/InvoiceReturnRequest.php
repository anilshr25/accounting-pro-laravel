<?php

namespace App\Http\Requests\Tenant\Invoice\Return;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'return_date' => 'required|date',
            'return_miti' => 'required|string|max:255',
            'shift' => 'required|string|max:255',
            'tax' => 'nullable|numeric',
            'sub_total' => 'required|numeric',
            'total' => 'required|numeric',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric',
            'items.*.rate' => 'required|numeric',
            'items.*.amount' => 'required|numeric',
        ];
    }
}
