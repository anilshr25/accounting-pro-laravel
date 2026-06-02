<?php

namespace App\Http\Resources\Tenant\Expenses;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpensesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title ?? null,
            'description' => $this->description,
            'expense_type' => $this->expense_type,
            'expense_date' => $this->expense_date?->format('Y-m-d'),
            'formatted_date' => $this->expense_date?->format('d M Y'),
            'expense_miti' => $this->expense_miti,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
        ];
    }
}
