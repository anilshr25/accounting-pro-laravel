<?php

namespace App\Http\Controllers\Tenant\Balance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Balance\BalanceRequest;
use App\Services\Tenant\Balance\BalanceService;

class BalanceController extends Controller
{
    protected $balance;

    public function __construct(BalanceService $balance)
    {
        $this->balance = $balance;
    }

    public function index(Request $request)
    {
        return $this->balance->paginate($request, 25);
    }

    public function store(BalanceRequest $request)
    {
        $balance = $this->balance->store($request->validated());
        if ($balance)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $balance = $this->balance->find($id);
        return response(['data' => $balance], 200);
    }



    public function update(BalanceRequest $request, $id)
    {
        $balance = $this->balance->update($id, $request->validated());
        if ($balance)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->balance->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
