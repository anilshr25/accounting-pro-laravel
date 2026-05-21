<?php

namespace App\Http\Resources\Tenant\Salary;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'month' => $this->month,
            'payment_date' => $this->payment_date,
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'status' => $this->status,
        ];
    }
}
