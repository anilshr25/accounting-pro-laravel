<?php

namespace App\Http\Controllers\Tenant\Ledger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Ledger\LedgerAdjustmentRequest;
use App\Services\Tenant\Ledger\LedgerService;

class LedgerAdjustmentController extends Controller
{
    protected $ledger;

    public function __construct(LedgerService $ledger)
    {
        $this->ledger = $ledger;
    }

    public function adjust(LedgerAdjustmentRequest $request)
    {
        $count = $this->ledger->adjustBalances(
            $request->party_type,
            $request->party_id,
            $request->date_from
        );

        if ($count === false) {
            return response(['status' => 'ERROR'], 500);
        }

        return response(['status' => 'OK', 'updated' => $count], 200);
    }
}
