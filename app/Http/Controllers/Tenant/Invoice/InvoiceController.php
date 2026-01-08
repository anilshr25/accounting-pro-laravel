<?php

namespace App\Http\Controllers\Tenant\Invoice;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Invoice\InvoiceRequest;
use App\Services\Tenant\Invoice\InvoiceService;

class InvoiceController extends Controller
{
    protected $invoice;

    public function __construct(InvoiceService $invoice)
    {
        $this->invoice = $invoice;
    }

    public function index(Request $request)
    {
        return $this->invoice->paginate($request, 25);
    }

    public function store(InvoiceRequest $request)
    {
        $invoice = $this->invoice->store($request->validated());
        if ($invoice)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function update(InvoiceRequest $request, $id)
    {
        $invoice = $this->invoice->update($id, $request->validated());
        if ($invoice)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->invoice->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
