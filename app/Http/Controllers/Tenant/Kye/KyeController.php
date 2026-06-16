<?php

namespace App\Http\Controllers\Tenant\Kye;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Kye\KyeRequest;
use App\Http\Resources\Tenant\Kye\KyeResource;
use App\Services\Tenant\Kye\KyeService;
use Illuminate\Http\Request;

class KyeController extends Controller
{
    protected $service;

    public function __construct(KyeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->paginate($request, 25);
    }

    public function store(KyeRequest $request)
    {
        $service = $this->service->store($request->validated());
        if ($service)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $service = $this->service->find($id, true);
        return response(['data' => $service], 200);
    }

    public function update(KyeRequest $request, $id)
    {
        $service = $this->service->update($id, $request->validated());
        if ($service)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->service->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
