<?php

namespace App\Http\Requests\Tenant\Invoice\Return;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'sales_return_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('invoice_returns', 'sales_return_number')->ignore($id),
            ],
            'return_date' => 'required|date',
            'return_miti' => 'required|string|max:255',
            'shift' => 'required|string|max:255',
            'tax' => 'nullable|numeric',
            'sub_total' => 'required|numeric',
            'total' => 'required|numeric',
            'type' => 'nullable|in:single,bulk',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric',
            'items.*.rate' => 'required|numeric',
            'items.*.amount' => 'required|numeric',
        ];
    }
}
