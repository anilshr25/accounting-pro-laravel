<?php

namespace App\Http\Controllers\Tenant\Cheque;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Tenant\Cheque\ChequeService;
use App\Services\Tenant\Customer\CustomerService;
use App\Services\Tenant\Supplier\SupplierService;
use App\Http\Requests\Tenant\Cheque\ChequeRequest;

class ChequeController extends Controller
{
    protected $cheque;
    protected $supplier;
    protected $customer;

    public function __construct(ChequeService $cheque, SupplierService $supplier, CustomerService $customer)
    {
        $this->cheque = $cheque;
        $this->supplier = $supplier;
        $this->customer = $customer;
    }

    public function index(Request $request)
    {
        return $this->cheque->paginate($request, 25);
    }

    public function store(ChequeRequest $request)
    {
        $data = $request->validated();
        if (isset($data['type']) && isset($data['pay_to'])) {
            $user = null;
            if (isset($data['status']) && $data['status'] == 'cleared') {
                if ($data['type'] == 'supplier') {
                    $user = $this->supplier->find($request->pay_to);
                }
                if ($data['type'] == 'customer') {
                    $user = $this->customer->find($request->pay_to);
                }
            }
            $cheque = $this->cheque->store($data, $user);
            if ($cheque)
                return response(['status' => 'OK'], 200);
            return response(['status' => 'ERROR'], 500);
        } else {
            return response(['status' => 'Supplier or Customer not found.'], 500);
        }
    }

    public function show($id)
    {
        $cheque = $this->cheque->find($id, true);
        return response(['data' => $cheque], 200);
    }

    public function update(ChequeRequest $request, $id)
    {
        $cheque = $this->cheque->update($id, $request->validated());
        if ($cheque)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->cheque->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    // public function chequeClear($id)
    // {
    //     $cheque = $this->cheque->find($id);
    //     if (!$cheque) {
    //         return response(['status' => 'ERROR', 'message' => 'Cheque not found'], 404);
    //     }
    //     if ($cheque->status === 'cleared') {
    //         return response(['status' => 'OK', 'message' => 'Cheque already cleared'], 200);
    //     }
    //     $user = $cheque->
    //     $updated = $this->cheque->chequeClear($id, ['status' => 'cleared'], $user);
    //     if ($updated) {
    //         return response(['status' => 'OK'], 200);
    //     }
    //     return response(['status' => 'ERROR'], 500);
    // }
}
