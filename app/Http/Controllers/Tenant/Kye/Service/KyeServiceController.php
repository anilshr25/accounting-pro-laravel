<?php

namespace App\Http\Controllers\Tenant\Kye\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Kye\Service\KyeServiceRequest;
use App\Services\Tenant\Kye\Service\KyeServicesService;
use Illuminate\Http\Request;

class KyeServiceController extends Controller
{
    protected $service;

    public function __construct(KyeServicesService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->paginate($request, 25);
    }

    public function store(KyeServiceRequest $request)
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

    public function update(KyeServiceRequest $request, $id)
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
