<?php

namespace App\Http\Controllers\Tenant\Cheque;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Cheque\ChequeRequest;
use App\Services\Tenant\Cheque\ChequeService;

class ChequeController extends Controller
{
    protected $cheque;

    public function __construct(ChequeService $cheque)
    {
        $this->cheque = $cheque;
    }

    public function index(Request $request)
    {
        return $this->cheque->paginate($request, 25);
    }

    public function store(ChequeRequest $request)
    {
        $cheque = $this->cheque->store($request->validated());
        if ($cheque)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
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
}
