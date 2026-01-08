<?php

namespace App\Http\Requests\Tenant\VendorPayment;

use Illuminate\Foundation\Http\FormRequest;

class VendorPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'nullable|integer',
            'date' => 'nullable|date',
            'miti' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'payment_type' => 'nullable|string|max:255',
            'shift' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|integer',
            'remarks' => 'nullable|string|max:255',
        ];
    }
}
