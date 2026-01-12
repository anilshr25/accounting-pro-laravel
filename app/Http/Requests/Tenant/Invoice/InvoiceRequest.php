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
            'invoice_miti' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'tax' => 'nullable|numeric',
            'sub_total' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'payment_type' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:255',
            'shift' => 'nullable|string|max:255',
            'sale_return' => 'nullable|boolean',
        ];
    }
}
