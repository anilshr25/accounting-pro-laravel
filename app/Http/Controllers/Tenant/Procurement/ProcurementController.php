<?php

namespace App\Http\Controllers\Tenant\Procurement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Procurement\ProcurementRequest;
use App\Services\Tenant\Procurement\ProcurementService;

class ProcurementController extends Controller
{
    protected $procurement;

    public function __construct(ProcurementService $procurement)
    {
        $this->procurement = $procurement;
    }

    public function index(Request $request)
    {
        return $this->procurement->paginate($request, 25);
    }

    public function store(ProcurementRequest $request)
    {
        $procurement = $this->procurement->store($request->validated());

        if ($procurement)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $procurement = $this->procurement->find($id, true);

        return response(['data' => $procurement], 200);
    }

    public function update(ProcurementRequest $request, $id)
    {
        $procurement = $this->procurement->update($id, $request->validated());

        if ($procurement)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->procurement->delete($id))
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }
}
