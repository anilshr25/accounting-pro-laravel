<?php

namespace App\Services\Tenant\Report;

use App\Models\Tenant\Credit\Credit;

class CreditReportService
{
    public function getReport($request)
    {
        $query = Credit::with('customer:id,name');

        $query->when($request->filled('customer_id'), function ($q) use ($request) {
            $q->where('customer_id', $request->customer_id);
        });

        $query->when($request->filled('type'), function ($q) use ($request) {
            $q->where('type', $request->type);
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        $query->when($request->filled('shift'), function ($q) use ($request) {
            $q->where('shift', $request->shift);
        });

        $query->when($request->filled('from_date') && $request->filled('to_date'), function ($q) use ($request) {
            $q->whereBetween('date', [
                $request->from_date,
                $request->to_date
            ]);
        });

        $query->when($request->filled('from_date') && !$request->filled('to_date'), function ($q) use ($request) {
            $q->whereDate('date', '>=', $request->from_date);
        });

        $query->when(!$request->filled('from_date') && $request->filled('to_date'), function ($q) use ($request) {
            $q->whereDate('date', '<=', $request->to_date);
        });

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;

            $q->where(function ($query) use ($search) {
                $query->whereHas('customer', function ($customer) use ($search) {
                    $customer->where('name', 'like', "%{$search}%");
                })
                ->orWhere('invoice_no', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        });

        $credits = $query->latest('date')->get();

        $summary = [
            'total_records' => $credits->count(),
            'total_credit_amount' => round($credits->sum('amount'), 2),
            'remaining_amount' => round(
                $credits->sum(function ($credit) {
                    return $credit->amount - $credit->return_amount;
                }),
                2
            ),
            'completed' => $credits->where('status', 'completed')->count(),
            'pending' => $credits->where('status', 'pending')->count(),
        ];

        $data = $credits->map(function ($credit) {

            return [
                'id' => $credit->id,
                'customer' => optional($credit->customer)->name,
                'invoice_no' => $credit->invoice_no,
                'type' => $credit->type,
                'shift' => $credit->shift,
                'status' => $credit->status,
                'amount' => $credit->amount,
                'description' => $credit->description,
                'date' => $credit->date?->format('Y-m-d'),
                'miti' => $credit->miti,
            ];
        });

        return [
            'data' => $data,
            'summary' => $summary,
        ];
    }
}
