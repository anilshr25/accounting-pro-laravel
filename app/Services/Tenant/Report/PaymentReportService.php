<?php

namespace App\Services\Tenant\Report;

use App\Models\Tenant\Payment\Payment;
use Illuminate\Support\Facades\DB;

class PaymentReportService
{
    public function getReport($request)
    {
        $payments = Payment::query()
            ->selectRaw("
                DATE(date) as date,
                miti,
                COUNT(*) as payment_count,
                SUM(amount) as total_amount
            ")
            ->when($request->filled('party_type'), function ($q) use ($request) {
                $q->where('party_type', $request->party_type);
            })
            ->when($request->filled('party_id'), function ($q) use ($request) {
                $q->where('party_id', $request->party_id);
            })
            ->when($request->filled('payment_method'), function ($q) use ($request) {
                $q->where('payment_method', $request->payment_method);
            })
            ->when($request->filled('shift'), function ($q) use ($request) {
                $q->where('shift', $request->shift);
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($q) use ($request) {
                $q->whereDate('date', '<=', $request->date_upto);
            })
            ->when($request->filled('miti_from'), function ($q) use ($request) {
                $q->where('miti', '>=', $request->miti_from);
            })
            ->when($request->filled('miti_upto'), function ($q) use ($request) {
                $q->where('miti', '<=', $request->miti_upto);
            })
            ->groupBy(DB::raw('DATE(date)'), 'miti')
            ->orderBy('date')
            ->get();

        $data = $payments->map(function ($payment) {
            return [
                'date' => $payment->date->format('Y-m-d'),
                'miti' => $payment->miti,
                'payment_count' => $payment->payment_count,
                'total_amount' => number_format($payment->total_amount, 2, '.', ''),
            ];
        });

        return [
            'data' => $data,
            'summary' => [
                'payment_count' => $data->sum('payment_count'),
                'total_amount' => number_format($payments->sum('total_amount'), 2, '.', ''),
            ],
        ];
    }
}
