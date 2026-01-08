<?php

namespace App\Http\Requests\Tenant\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'nullable|integer',
            'purchase_invoice_number' => 'nullable|string|max:255',
            'order_date' => 'nullable|date',
            'received_date' => 'nullable|date',
            'tax' => 'nullable|numeric',
            'sub_total' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'status' => 'nullable|string|max:255',
            'received_by' => 'nullable|string|max:255',
        ];
    }
}
