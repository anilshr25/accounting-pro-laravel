<?php

namespace App\Http\Controllers\Tenant\Invoice\Return;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Invoice\Return\InvoiceReturnRequest;
use App\Services\Tenant\Invoice\Return\InvoiceReturnService;
use App\Http\Resources\Tenant\Invoice\Return\InvoiceReturnResource;

class InvoiceReturnController extends Controller
{
    protected $invoice_return;

    public function __construct(InvoiceReturnService $invoice_return)
    {
        $this->invoice_return = $invoice_return;
    }

    public function index(Request $request)
    {
        $data = $this->invoice_return->paginate($request);

        return InvoiceReturnResource::collection($data);
    }

    public function store(InvoiceReturnRequest $request)
    {
        $invoice_return = $this->invoice_return->store($request->validated());
        if ($invoice_return)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $invoice_return = $this->invoice_return->find($id, true);
        return response(['data' => $invoice_return], 200);
    }

    public function update(InvoiceReturnRequest $request, $id)
    {
        $invoice_return = $this->invoice_return->update($id, $request->validated());
        if ($invoice_return)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->invoice_return->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
