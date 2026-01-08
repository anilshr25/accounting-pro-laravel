<?php

namespace App\Http\Controllers\Tenant\PurchaseOrder\Item;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\PurchaseOrder\Item\PurchaseOrderItemRequest;
use App\Services\Tenant\PurchaseOrder\Item\PurchaseOrderItemService;

class PurchaseOrderItemController extends Controller
{
    protected $purchase_order_item;

    public function __construct(PurchaseOrderItemService $purchase_order_item)
    {
        $this->purchase_order_item = $purchase_order_item;
    }

    public function index(Request $request)
    {
        return $this->purchase_order_item->paginate($request, 25);
    }

    public function store(PurchaseOrderItemRequest $request)
    {
        $purchase_order_item = $this->purchase_order_item->store($request->validated());
        if ($purchase_order_item)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function update(PurchaseOrderItemRequest $request, $id)
    {
        $purchase_order_item = $this->purchase_order_item->update($id, $request->validated());
        if ($purchase_order_item)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->purchase_order_item->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
