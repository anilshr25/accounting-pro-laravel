<?php

namespace App\Http\Requests\Tenant\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required',
            'user_id' => 'required|integer',
            'date' => 'nullable|date',
            'miti' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'payment_method' => 'required',
            'shift' => 'nullable|in:morning,evening',
            'transaction_id' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ];
    }
}
