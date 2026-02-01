<?php

namespace App\Services\Tenant\Dashboard;

use Carbon\Carbon;
use App\Models\Tenant\Invoice\Invoice;
use App\Models\Tenant\Invoice\Return\InvoiceReturn;
use App\Models\Tenant\Purchase\Order\PurchaseOrder;
use App\Models\Tenant\Purchase\Return\PurchaseReturn;
use App\Models\Tenant\Credit\Credit;
use App\Models\Tenant\Cheque\Cheque;


class DashboardService
{
    protected $invoice;
    protected $invoiceReturn;
    protected $purchaseOrder;
    protected $purchaseReturn;
    protected $credit;
    protected $cheque;

    public function __construct(
        Invoice $invoice,
        InvoiceReturn $invoiceReturn,
        PurchaseOrder $purchaseOrder,
        PurchaseReturn $purchaseReturn,
        Credit $credit,
        Cheque $cheque,
    ) {
        $this->invoice = $invoice;
        $this->invoiceReturn = $invoiceReturn;
        $this->purchaseOrder = $purchaseOrder;
        $this->purchaseReturn = $purchaseReturn;
        $this->credit = $credit;
        $this->cheque = $cheque;
    }

    public function getSummary(array $filters = [])
    {
        $period = $filters['period'] ?? 'daily';
        $endDate = isset($filters['end_date']) ? Carbon::parse($filters['end_date'])->endOfDay() : Carbon::yesterday()->endOfDay();
        $startDate = isset($filters['start_date']) ? Carbon::parse($filters['start_date'])->startOfDay() : match ($period) {
            'weekly' => $endDate->copy()->subWeek()->startOfDay(),
            'monthly' => $endDate->copy()->subMonth()->startOfDay(),
            default => $endDate->copy()->startOfDay(),
        };

        $totalSales = $this->invoice
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total');

        $salesReturns = $this->invoiceReturn
            ->whereBetween('return_date', [$startDate, $endDate])
            ->sum('total');

        $salesBreakdown = $this->invoice
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->selectRaw('payment_type, SUM(total) as total')
            ->groupBy('payment_type')
            ->get()
            ->concat([
                (object)[
                    'type' => 'return',
                    'total' => $salesReturns,
                ]
            ]);

        $totalPurchases = $this->purchaseOrder
            ->whereBetween('received_date', [$startDate, $endDate])
            ->sum('total');

        $purchaseReturns = $this->purchaseReturn
            ->whereBetween('return_date', [$startDate, $endDate])
            ->sum('total');

        $purchaseBreakdown = collect([
            (object)[
                'type' => 'purchase',
                'total' => $totalPurchases,
            ],
            (object)[
                'type' => 'return',
                'total' => $purchaseReturns,
            ]
        ]);

        $credit = $this->credit
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $customerChequeAmount = $this->cheque
            ->where('type', 'customer')
            ->where('status', 'pending')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $supplierChequeAmount = $this->cheque
            ->where('type', 'supplier')
            ->where('status', 'pending')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $chequeBalance = $supplierChequeAmount;

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_sales' => $totalSales - $salesReturns,
            'sales_breakdown' => $salesBreakdown,
            'total_purchases' => $totalPurchases - $purchaseReturns,
            'purchase_breakdown' => $purchaseBreakdown,
            'outstanding_credit' => $credit,
            'cheque_balance' => $chequeBalance,
            'customer_cheque_balance' => $customerChequeAmount,
        ];
    }
}
