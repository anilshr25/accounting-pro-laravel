<?php

namespace App\Http\Controllers\Tenant\Purchase\Return\Item;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Purchase\Return\Item\PurchaseReturnItemRequest;
use App\Services\Tenant\Purchase\Return\Item\PurchaseReturnItemService;

class PurchaseReturnItemController extends Controller
{
    protected $purchase_return_item;

    public function __construct(PurchaseReturnItemService $purchase_return_item)
    {
        $this->purchase_return_item = $purchase_return_item;
    }

    public function index(Request $request)
    {
        return $this->purchase_return_item->paginate($request, 25);
    }

    public function store(PurchaseReturnItemRequest $request)
    {
        $purchase_return_item = $this->purchase_return_item->store($request->validated());
        if ($purchase_return_item)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $purchase_return_item = $this->purchase_return_item->find($id, true);
        return response(['data' => $purchase_return_item], 200);
    }



    public function update(PurchaseReturnItemRequest $request, $id)
    {
        $purchase_return_item = $this->purchase_return_item->update($id, $request->validated());
        if ($purchase_return_item)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->purchase_return_item->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
