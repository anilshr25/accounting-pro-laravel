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
        $type = $filters['type'] ?? 'monthly';

        $date  = null;
        $month = null;
        $year  = null;

        if ($type === 'daily') {
            $date = $filters['date'] ?? Carbon::today()->toDateString();

            $startDate = Carbon::parse($date)->startOfDay();
            $endDate   = Carbon::parse($date)->endOfDay();
        } elseif ($type === 'yearly') {
            $year = $filters['year'] ?? Carbon::now()->year;

            $startDate = Carbon::create($year)->startOfYear();
            $endDate   = Carbon::create($year)->endOfYear();
        } else {
            $month = $filters['month'] ?? Carbon::now()->month;
            $year  = $filters['year'] ?? Carbon::now()->year;

            $startDate = Carbon::create($year, $month)->startOfMonth();
            $endDate   = Carbon::create($year, $month)->endOfMonth();

            $type = 'monthly';
        }

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

        $purchaseBreakdown = collect([(object)['type' => 'purchase', 'total' => $totalPurchases,], (object)['type' => 'return', 'total' => $purchaseReturns,]]);

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

        return [
            'filter_type' => $type,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_sales' => $totalSales - $salesReturns,
            'sales_breakdown' => $salesBreakdown,
            'total_purchases' => $totalPurchases - $purchaseReturns,
            'purchase_breakdown' => $purchaseBreakdown,
            'outstanding_credit' => $credit,
            'cheque_balance' => $supplierChequeAmount,
            'customer_cheque_balance' => $customerChequeAmount,

            'type'  => $type,
            'date'  => $date,
            'month' => $month,
            'year'  => $year,
        ];
    }
}
