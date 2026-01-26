<?php

namespace App\Http\Requests\Tenant\Purchase\Return;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_invoice_number' => 'required|exists:purchase_orders,purchase_invoice_number',
            'return_date' => 'required|date',
            'return_miti' => 'required|string|max:255',
            'tax' => 'nullable|numeric',
            'sub_total' => 'required|numeric',
            'total' => 'required|numeric',
            'status' => 'nullable|string|max:255',
            'returned_by' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric',
            'items.*.rate' => 'required|numeric',
            'items.*.amount' => 'required|numeric',
        ];
    }
}
