<?php

namespace App\Http\Controllers\Tenant\Procurement\Item;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Procurement\Item\ProcurementItemRequest;
use App\Services\Tenant\Procurement\Item\ProcurementItemService;

class ProcurementItemController extends Controller
{
    protected $procurement_item;

    public function __construct(ProcurementItemService $procurement_item)
    {
        $this->procurement_item = $procurement_item;
    }

    public function index(Request $request)
    {
        return $this->procurement_item->paginate($request, 25);
    }

    public function store(ProcurementItemRequest $request)
    {
        $procurement_item = $this->procurement_item->store($request->validated());
        if ($procurement_item)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $procurement_item = $this->procurement_item->find($id, true);
        return response(['data' => $procurement_item], 200);
    }

    public function update(ProcurementItemRequest $request, $id)
    {
        $procurement_item = $this->procurement_item->update($id, $request->validated());
        if ($procurement_item)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->procurement_item->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
