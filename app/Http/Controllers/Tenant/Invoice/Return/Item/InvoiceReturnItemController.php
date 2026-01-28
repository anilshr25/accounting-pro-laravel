<?php

namespace App\Http\Controllers\Tenant\Invoice\Return\Item;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Invoice\Return\Item\InvoiceReturnItemRequest;
use App\Services\Tenant\Invoice\Return\Item\InvoiceReturnItemService;

class InvoiceReturnItemController extends Controller
{
    protected $invoice_return_item;

    public function __construct(InvoiceReturnItemService $invoice_return_item)
    {
        $this->invoice_return_item = $invoice_return_item;
    }

    public function index(Request $request)
    {
        return $this->invoice_return_item->paginate($request, 25);
    }

    public function store(InvoiceReturnItemRequest $request)
    {
        $invoice_return_item = $this->invoice_return_item->store($request->validated());
        if ($invoice_return_item)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $invoice_return_item = $this->invoice_return_item->find($id, true);
        return response(['data' => $invoice_return_item], 200);
    }



    public function update(InvoiceReturnItemRequest $request, $id)
    {
        $invoice_return_item = $this->invoice_return_item->update($id, $request->validated());
        if ($invoice_return_item)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->invoice_return_item->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
