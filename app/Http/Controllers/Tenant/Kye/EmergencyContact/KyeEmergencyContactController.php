<?php

namespace App\Http\Controllers\Tenant\Kye\EmergencyContact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Kye\EmergencyContact\KyeEmergencyContactRequest;
use App\Services\Tenant\Kye\EmergencyContact\KyeEmergencyContactService;
use Illuminate\Http\Request;

class KyeEmergencyContactController extends Controller
{
    protected $emergencyContactService;

    public function __construct(KyeEmergencyContactService $emergencyContactService)
    {
        $this->emergencyContactService = $emergencyContactService;
    }

    public function index(Request $request)
    {
        return $this->emergencyContactService->paginate($request, 25);
    }

    public function store(KyeEmergencyContactRequest $request)
    {
        $emergencyContact = $this->emergencyContactService->store($request->validated());
        if ($emergencyContact)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $emergencyContact = $this->emergencyContactService->find($id, true);
        return response(['data' => $emergencyContact], 200);
    }

    public function update(KyeEmergencyContactRequest $request, $id)
    {
        $emergencyContact = $this->emergencyContactService->update($id, $request->validated());
        if ($emergencyContact)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->emergencyContactService->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
