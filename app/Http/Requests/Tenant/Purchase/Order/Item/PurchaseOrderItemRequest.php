<?php

namespace App\Http\Requests\Tenant\Purchase\Order\Item;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_order_id' => 'nullable|integer',
            'description' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric',
            'rate' => 'nullable|numeric',
            'amount' => 'nullable|numeric',
        ];
    }
}
