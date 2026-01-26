<?php

namespace App\Http\Requests\Tenant\Purchase\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'purchase_invoice_number' => [
                'required',
                'string'
            ],
            'order_date' => 'nullable|date',
            'order_date_miti' => 'nullable|string|max:255',
            'received_date' => 'nullable|date',
            'received_date_miti' => 'nullable|string|max:255',
            'tax' => 'nullable|numeric',
            'sub_total' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'status' => 'nullable|string|max:255',
            'received_by' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'credit' => 'nullable|numeric',
            'debit' => 'nullable|numeric',
            'cheque_id' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'balance' => 'nullable|numeric',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric',
            'items.*.rate' => 'required|numeric',
            'items.*.amount' => 'required|numeric',
        ];
    }
}
