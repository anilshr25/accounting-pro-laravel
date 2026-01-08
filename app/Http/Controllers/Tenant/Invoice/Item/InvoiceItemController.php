<?php

namespace App\Http\Controllers\Tenant\Invoice\Item;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Invoice\Item\InvoiceItemRequest;
use App\Services\Tenant\Invoice\Item\InvoiceItemService;

class InvoiceItemController extends Controller
{
    protected $invoice_item;

    public function __construct(InvoiceItemService $invoice_item)
    {
        $this->invoice_item = $invoice_item;
    }

    public function index(Request $request)
    {
        return $this->invoice_item->paginate($request, 25);
    }

    public function store(InvoiceItemRequest $request)
    {
        $invoice_item = $this->invoice_item->store($request->validated());
        if ($invoice_item)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function update(InvoiceItemRequest $request, $id)
    {
        $invoice_item = $this->invoice_item->update($id, $request->validated());
        if ($invoice_item)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->invoice_item->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
