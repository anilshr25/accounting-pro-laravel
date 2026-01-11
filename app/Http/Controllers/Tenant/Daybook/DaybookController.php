<?php

namespace App\Http\Controllers\Tenant\Daybook;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Daybook\DaybookRequest;
use App\Services\Tenant\Daybook\DaybookService;

class DaybookController extends Controller
{
    protected $daybook;

    public function __construct(DaybookService $daybook)
    {
        $this->daybook = $daybook;
    }

    public function index(Request $request)
    {
        return $this->daybook->paginate($request, 25);
    }

    public function store(DaybookRequest $request)
    {
        $daybook = $this->daybook->store($request->validated());
        if ($daybook)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $daybook = $this->daybook->find($id, true);
        return response(['data' => $daybook], 200);
    }

    public function update(DaybookRequest $request, $id)
    {
        $daybook = $this->daybook->update($id, $request->validated());
        if ($daybook)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->daybook->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
