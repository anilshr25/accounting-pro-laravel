<?php

namespace App\Http\Controllers\Tenant\Credit;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Credit\CreditRequest;
use App\Services\Tenant\Credit\CreditService;

class CreditController extends Controller
{
    protected $credit;

    public function __construct(CreditService $credit)
    {
        $this->credit = $credit;
    }

    public function index(Request $request)
    {
        return $this->credit->paginate($request, 25);
    }

    public function store(CreditRequest $request)
    {
        $credit = $this->credit->store($request->validated());
        if ($credit)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $credit = $this->credit->find($id);
        return response(['data' => $credit], 200);
    }



    public function update(CreditRequest $request, $id)
    {
        $credit = $this->credit->update($id, $request->validated());
        if ($credit)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->credit->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
