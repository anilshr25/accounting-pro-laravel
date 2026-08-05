<?php

namespace App\Http\Resources\Tenant\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray($request)
    {

        if (isset($this['graph'])) {
            return [
                'meta' => [
                    'type' => $this['filter_type'],
                    'start_date' => $this['start_date'],
                    'end_date' => $this['end_date'],
                    'fiscal_year' => $this['fiscal_year'] ?? '2083/84',
                ],

                'labels' => collect($this['graph'])->pluck('label'),
                'sales' => collect($this['graph'])->pluck('sales'),
                'purchases' => collect($this['graph'])->pluck('purchases'),
            ];
        }

        return [
            'summary' => [
                'total_sales'        => number_format($this['total_sales'] ?? 0, 3, '.', ''),
                'total_purchases'    => number_format($this['total_purchases'] ?? 0, 3, '.', ''),
                'outstanding_credit' => number_format($this['outstanding_credit'] ?? 0, 3, '.', ''),
                'cheque_balance'     => number_format($this['cheque_balance'] ?? 0, 3, '.', ''),
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
                'fiscal_year' => $this['fiscal_year'] ?? '2083/84',
            ],
        ];
    }
}
