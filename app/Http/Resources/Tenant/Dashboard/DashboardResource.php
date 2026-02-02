<?php

namespace App\Http\Resources\Tenant\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'summary' => [
                'total_sales'        => $this['total_sales'] ?? 0,
                'total_purchases'    => $this['total_purchases'] ?? 0,
                'outstanding_credit' => $this['outstanding_credit'] ?? 0,
                'cheque_balance'     => $this['cheque_balance'] ?? 0,
                'customer_cheque_balance' => $this['customer_cheque_balance'] ?? 0,
            ],

            'breakdown' => [
                'sales'     => $this['sales_breakdown'] ?? [],
                'purchases' => $this['purchase_breakdown'] ?? [],
            ],

            'meta' => [
                'type'  => $this['type'] ?? 'monthly',
                'date'  => $this['date'] ?? null,
                'month' => $this['month'] ?? null,
                'year'  => $this['year'] ?? null,
            ],
        ];
    }
}
