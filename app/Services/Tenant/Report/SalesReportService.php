<?php

namespace App\Services\Tenant\Report;

use App\Models\Tenant\Invoice\Invoice;
use App\Models\Tenant\Invoice\Return\InvoiceReturn;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    public function getReport($request)
    {
        $sales = Invoice::query()
            ->selectRaw("
                DATE(invoice_date) as date,
                invoice_miti,
                COUNT(*) as invoice_count,
                SUM(sub_total) as sub_total,
                SUM(total) as gross_sales
            ")
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('invoice_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($q) use ($request) {
                $q->whereDate('invoice_date', '<=', $request->date_upto);
            })
            ->when($request->filled('customer_id'), function ($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            })
            ->when($request->filled('payment_type'), function ($q) use ($request) {
                $q->where('payment_type', $request->payment_type);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('shift'), function ($q) use ($request) {
                $q->where('shift', $request->shift);
            })
            ->groupBy(DB::raw('DATE(invoice_date)'), 'invoice_miti')
            ->get()
            ->keyBy('date');

        $returns = InvoiceReturn::query()
            ->selectRaw("
                DATE(return_date) as date,
                COUNT(*) as return_count,
                SUM(total) as return_total
            ")
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('return_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($q) use ($request) {
                $q->whereDate('return_date', '<=', $request->date_upto);
            })
            ->when($request->filled('customer_id'), function ($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            })
            ->groupBy(DB::raw('DATE(return_date)'))
            ->get()
            ->keyBy('date');

        $dates = collect($sales->keys())
            ->merge($returns->keys())
            ->unique()
            ->sort()
            ->values();

        $data = $dates->map(function ($date) use ($sales, $returns) {

            $sale = $sales[$date] ?? null;
            $return = $returns[$date] ?? null;

            $grossSales = $sale->gross_sales ?? 0;
            $salesReturn = $return->return_total ?? 0;

            return [
                'date' => $date,
                'miti' => $sale->invoice_miti ?? null,
                'invoice_count' => $sale->invoice_count ?? 0,
                'return_count' => $return->return_count ?? 0,
                'gross_sales' => number_format($grossSales, 2, '.', ''),
                'sales_return' => number_format($salesReturn, 2, '.', ''),
                'net_sales' => number_format($grossSales - $salesReturn, 2, '.', ''),
            ];
        });

        return [
            'data' => $data,
            'summary' => [
                'invoice_count' => $data->sum('invoice_count'),
                'return_count' => $data->sum('return_count'),
                'gross_sales' => number_format($data->sum('gross_sales'), 2, '.', ''),
                'sales_return' => number_format($data->sum('sales_return'), 2, '.', ''),
                'net_sales' => number_format($data->sum('net_sales'), 2, '.', ''),
            ],
        ];
    }
}
