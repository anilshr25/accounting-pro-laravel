<?php

namespace App\Http\Controllers\Tenant\Kye\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Kye\Address\KyeAddressRequest;
use App\Services\Tenant\Kye\Address\KyeAddressService;
use Illuminate\Http\Request;

class KyeAddressController extends Controller
{
    protected $addressService;

    public function __construct(KyeAddressService $addressService)
    {
        $this->addressService = $addressService;
    }

    public function index(Request $request)
    {
        return $this->addressService->paginate($request, 25);
    }

    public function store(KyeAddressRequest $request)
    {
        $address = $this->addressService->store($request->validated());
        if ($address)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $address = $this->addressService->find($id, true);
        return response(['data' => $address], 200);
    }

    public function update(KyeAddressRequest $request, $id)
    {
        $address = $this->addressService->update($id, $request->validated());
        if ($address)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->addressService->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
