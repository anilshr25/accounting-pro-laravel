<?php

namespace App\Http\Resources\Tenant\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'total_sales' => $this['total_sales'],
            'sales_breakdown' => $this['sales_breakdown'],
            'total_purchases' => $this['total_purchases'],
            'purchase_breakdown' => $this['purchase_breakdown'],
            'outstanding_credit' => $this['outstanding_credit'],
            'cheque_balance' => $this['cheque_balance'],
            'customer_cheque_balance' => $this['customer_cheque_balance'],
        ];
    }
}
