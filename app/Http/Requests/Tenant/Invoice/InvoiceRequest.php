<?php

namespace App\Http\Requests\Tenant\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|integer',
            'invoice_miti' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'tax' => 'required|numeric',
            'sub_total' => 'required|numeric',
            'total' => 'required|numeric',
            'payment_type' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:255',
            'shift' => 'required|string|max:255',
            'sale_return' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric',
            'items.*.rate' => 'required|numeric',
            'items.*.amount' => 'required|numeric',
        ];
    }
}
