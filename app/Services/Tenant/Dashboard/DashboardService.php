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

    public function getSummary($date = null)
    {
        $date = $date ? Carbon::parse($date)->endOfDay() : Carbon::yesterday()->endOfDay();

        $totalSales = $this->invoice->sum('total');
        $salesReturns = $this->invoiceReturn->sum('total');

        $salesBreakdown = $this->invoice
            ->selectRaw('payment_type, SUM(total) as total')
            ->groupBy('payment_type')
            ->get()
            ->concat([
                (object)[
                    'type' => 'return',
                    'total' => $salesReturns,
                ]
            ]);
        $totalPurchases = $this->purchaseOrder->sum('total');
        $purchaseReturns = $this->purchaseReturn->sum('total');
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

        $credit = $this->credit->whereDate('date', '<=', $date)->sum('amount');

        $customerChequeAmount = $this->cheque
            ->where('type', 'customer')
            ->where('status', 'pending')
            ->whereDate('date', '<=', $date)
            ->sum('amount');

        $supplierChequeAmount = $this->cheque
            ->where('type', 'supplier')
            ->where('status', 'pending')
            ->whereDate('date', '<=', $date)
            ->sum('amount');

        $chequeBalance =  $supplierChequeAmount;


        return [
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
