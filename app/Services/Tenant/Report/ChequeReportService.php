<?php

namespace App\Services\Tenant\Report;

use App\Models\Tenant\Cheque\Cheque;
use Illuminate\Support\Facades\DB;

class ChequeReportService
{
    public function getReport($request)
    {
        $report = Cheque::query()
            ->selectRaw("
                DATE(date) as date,
                miti,
                COUNT(*) as cheque_count,
                SUM(amount) as total_amount,
                SUM(CASE WHEN type = 'received' THEN amount ELSE 0 END) as received_amount,
                SUM(CASE WHEN status = 'cleared' THEN amount ELSE 0 END) as cleared_amount,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'cancelled' THEN amount ELSE 0 END) as cancelled_amount
            ")
            ->when($request->filled('bank_account_id'), function ($q) use ($request) {
                $q->where('bank_account_id', $request->bank_account_id);
            })
            ->when($request->filled('party_type'), function ($q) use ($request) {
                $q->where('party_type', $request->party_type);
            })
            ->when($request->filled('party_id'), function ($q) use ($request) {
                $q->where('party_id', $request->party_id);
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($q) use ($request) {
                $q->whereDate('date', '<=', $request->date_upto);
            })
            ->groupBy(DB::raw('DATE(date)'), 'miti')
            ->orderBy('date')
            ->get();

        return [
            'data' => $report->map(function ($row) {
                return [
                    'date' => $row->date?->format('Y-m-d'),
                    'miti' => $row->miti,
                    'cheque_count' => (int) $row->cheque_count,
                    'received_amount' => number_format($row->received_amount, 2, '.', ''),
                    'cleared_amount' => number_format($row->cleared_amount, 2, '.', ''),
                    'pending_amount' => number_format($row->pending_amount, 2, '.', ''),
                    'cancelled_amount' => number_format($row->cancelled_amount, 2, '.', ''),
                    'total_amount' => number_format($row->total_amount, 2, '.', ''),
                ];
            }),

            'summary' => [
                'cheque_count' => $report->sum('cheque_count'),
                'received_amount' => number_format($report->sum('received_amount'), 2, '.', ''),
                'cleared_amount' => number_format($report->sum('cleared_amount'), 2, '.', ''),
                'pending_amount' => number_format($report->sum('pending_amount'), 2, '.', ''),
                'cancelled_amount' => number_format($report->sum('cancelled_amount'), 2, '.', ''),
                'total_amount' => number_format($report->sum('total_amount'), 2, '.', ''),
            ],
        ];
    }
}
