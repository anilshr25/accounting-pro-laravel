<?php

namespace App\Services\Tenant\Daybook;

use App\Models\Tenant\Daybook\Daybook;
use App\Models\Tenant\Balance\Balance;
use App\Http\Resources\Tenant\Daybook\DaybookResource;
use App\Models\Tenant\Invoice\Return\InvoiceReturn;
use App\Models\Tenant\Cheque\Cheque;
use App\Models\Tenant\Credit\Credit;
use App\Models\Tenant\Payment\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DaybookService
{
    protected $daybook;
    public function __construct(Daybook $daybook)
    {
        $this->daybook = $daybook;
    }

    public function paginate(Request $request, int $limit = 25)
    {
        if (!$request->filled('date')) {
            throw new \Exception('Date is required');
        }

        $date = Carbon::parse($request->date);
        $shift = $request->shift;

        $start = $date->copy()->startOfDay();
        $end   = $date->copy()->endOfDay();


        $paymentsQuery = Payment::whereBetween('date', [$start, $end])
            ->when($shift, fn($q) => $q->where('shift', $shift));

        $cashSales = (clone $paymentsQuery)->where('payment_method', 'cash')->sum('amount');
        $fonepaySales = (clone $paymentsQuery)->where('payment_method', 'fonepay')->sum('amount');
        $cardpaySales = (clone $paymentsQuery)->where('payment_method', 'cardpay')->sum('amount');

        $creditSales = Credit::whereBetween('date', [$start, $end])
            ->when($shift, fn($q) => $q->where('shift', $shift))
            ->sum('amount');

        $chequeSales = Cheque::whereBetween('date', [$start, $end])
            ->when($shift, fn($q) => $q->where('shift', $shift))
            ->sum('amount');

        $salesReturns = InvoiceReturn::whereBetween('return_date', [$start, $end])
            ->when($shift, fn($q) => $q->where('shift', $shift))
            ->sum('total');

        $grossSales = $cashSales + $fonepaySales + $cardpaySales + $creditSales + $chequeSales;
        $netSales = $grossSales - $salesReturns;


        $openingBalance = Balance::whereDate('date', $date)
            ->when($shift, fn($q) => $q->where('shift', $shift))
            ->sum('opening_balance');

        $closingBalance = Balance::whereDate('date', $date)
            ->when($shift, fn($q) => $q->where('shift', $shift))
            ->sum('closing_balance');

        if ($closingBalance == 0) {
            $closingBalance = $openingBalance + $netSales;
        }

        $daybooks = $this->daybook
            ->whereBetween('date', [$start, $end])
            ->when($shift, fn($q) => $q->where('shift', $shift))
            ->orderBy('date', 'asc')
            ->paginate($request->limit ?? $limit);

        return response()->json([
            'date' => $request->date,
            'shift' => $shift ?? 'all',
            'summary' => [
                'openingBalance'  => (float) $openingBalance,
                'cashSales'       => (float) $cashSales,
                'fonepaySales'    => (float) $fonepaySales,
                'cardpaySales'    => (float) $cardpaySales,
                'creditSales'     => (float) $creditSales,
                'customerCheques' => (float) $chequeSales,
                'salesReturns'    => (float) $salesReturns,
                'grossSales'      => (float) $grossSales,
                'totalProfit'     => (float) $netSales,
                'closingBalance'  => (float) $closingBalance,
            ],
            'data' => DaybookResource::collection($daybooks),
            'pagination' => [
                'current_page' => $daybooks->currentPage(),
                'last_page'    => $daybooks->lastPage(),
                'per_page'     => $daybooks->perPage(),
                'total'        => $daybooks->total(),
            ],
        ]);
    }


    public function store($data)
    {
        try {
            return $this->daybook->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $daybook = $this->daybook->find($id);
        if (!$daybook) {
            return null;
        }
        return $resource ? new DaybookResource($daybook) : $daybook;
    }

    public function update($id, $data)
    {
        try {
            $daybook = $this->find($id);
            if (!$daybook) {
                return false;
            }
            return $daybook->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $daybook = $this->find($id);
            if (!$daybook) {
                return false;
            }
            return $daybook->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
