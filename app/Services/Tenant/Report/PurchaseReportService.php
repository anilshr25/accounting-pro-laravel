<?php

namespace App\Services\Tenant\Report;

use App\Models\Tenant\Purchase\Order\PurchaseOrder;
use App\Models\Tenant\Purchase\Return\PurchaseReturn;
use Illuminate\Support\Facades\DB;

class PurchaseReportService
{
    public function getReport($request)
    {
        $purchases = PurchaseOrder::query()
            ->selectRaw("
                DATE(order_date) as date,
                order_miti
                COUNT(*) as purchase_count,
                SUM(sub_total) as sub_total,
                SUM(total) as gross_purchase
            ")
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->date_upto);
            })
            ->when($request->filled('supplier_id'), function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->groupBy(DB::raw('DATE(order_date)'))
            ->get()
            ->keyBy('date');

        $returns = PurchaseReturn::query()
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
            ->when($request->filled('supplier_id'), function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->groupBy(DB::raw('DATE(return_date)'))
            ->get()
            ->keyBy('date');

        $dates = collect($purchases->keys())
            ->merge($returns->keys())
            ->unique()
            ->sort()
            ->values();

        $data = $dates->map(function ($date) use ($purchases, $returns) {

            $purchase = $purchases[$date] ?? null;
            $return = $returns[$date] ?? null;

            $grossPurchase = $purchase->gross_purchase ?? 0;
            $purchaseReturn = $return->return_total ?? 0;

            return [
                'date' => $date,
                'purchase_count' => $purchase->purchase_count ?? 0,
                'return_count' => $return->return_count ?? 0,
                'gross_purchase' => number_format($grossPurchase, 2, '.', ''),
                'purchase_return' => number_format($purchaseReturn, 2, '.', ''),
                'net_purchase' => number_format($grossPurchase - $purchaseReturn, 2, '.', ''),
            ];
        });

        return [
            'data' => $data,
            'summary' => [
                'purchase_count' => $data->sum('purchase_count'),
                'return_count' => $data->sum('return_count'),
                'gross_purchase' => number_format($data->sum('gross_purchase'), 2, '.', ''),
                'purchase_return' => number_format($data->sum('purchase_return'), 2, '.', ''),
                'net_purchase' => number_format($data->sum('net_purchase'), 2, '.', ''),
            ],
        ];
    }
}
