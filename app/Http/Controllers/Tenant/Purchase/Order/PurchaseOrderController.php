<?php

namespace App\Http\Controllers\Tenant\Purchase\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Purchase\Order\PurchaseOrderRequest;
use App\Services\Tenant\Purchase\Order\PurchaseOrderService;

class PurchaseOrderController extends Controller
{
    protected $purchase_order;

    public function __construct(PurchaseOrderService $purchase_order)
    {
        $this->purchase_order = $purchase_order;
    }

    public function index(Request $request)
    {
        return $this->purchase_order->paginate($request, 25);
    }

    public function store(PurchaseOrderRequest $request)
    {
        $purchase_order = $this->purchase_order->store($request->validated());
        if ($purchase_order)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $purchase_order = $this->purchase_order->find($id, true);
        return response(['data' => $purchase_order], 200);
    }

    public function update(PurchaseOrderRequest $request, $id)
    {
        $purchase_order = $this->purchase_order->update($id, $request->validated());
        if ($purchase_order)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->purchase_order->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
