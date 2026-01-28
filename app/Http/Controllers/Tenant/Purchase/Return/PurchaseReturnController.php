<?php

namespace App\Http\Controllers\Tenant\Purchase\Return;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Purchase\Return\PurchaseReturnRequest;
use App\Services\Tenant\Purchase\Return\PurchaseReturnService;

class PurchaseReturnController extends Controller
{
    protected $purchase_return;

    public function __construct(PurchaseReturnService $purchase_return)
    {
        $this->purchase_return = $purchase_return;
    }

    public function index(Request $request)
    {
        return $this->purchase_return->paginate($request, 25);
    }

    public function store(PurchaseReturnRequest $request)
    {
        $purchase_return = $this->purchase_return->store($request->validated());
        if ($purchase_return)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $purchase_return = $this->purchase_return->find($id, true);
        return response(['data' => $purchase_return], 200);
    }

    public function update(PurchaseReturnRequest $request, $id)
    {
        $purchase_return = $this->purchase_return->update($id, $request->validated());
        if ($purchase_return)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->purchase_return->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
