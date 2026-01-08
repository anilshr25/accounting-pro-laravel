<?php

namespace App\Http\Requests\Tenant\Customer\Payment;

use Illuminate\Foundation\Http\FormRequest;

class CustomerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|integer',
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
