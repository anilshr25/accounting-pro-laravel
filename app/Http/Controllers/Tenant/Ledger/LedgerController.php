<?php

namespace App\Http\Controllers\Tenant\Ledger;

use App\Services\Tenant\Ledger\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LedgerController extends Controller
{
    protected $ledger;

    public function __construct(LedgerService $ledger)
    {
        $this->ledger = $ledger;
    }

    public function index(Request $request)
    {
        return $this->ledger->paginate($request, 25);
    }
}
