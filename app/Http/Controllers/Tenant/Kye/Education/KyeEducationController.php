<?php

namespace App\Http\Controllers\Tenant\Kye\Education;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Kye\Education\KyeEducationRequest;
use App\Services\Tenant\Kye\Education\KyeEducationService;
use Illuminate\Http\Request;

class KyeEducationController extends Controller
{
    protected $educationService;

    public function __construct(KyeEducationService $educationService)
    {
        $this->educationService = $educationService;
    }

    public function index(Request $request)
    {
        return $this->educationService->paginate($request, 25);
    }

    public function store(KyeEducationRequest $request)
    {
        $education = $this->educationService->store($request->validated());
        if ($education)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $education = $this->educationService->find($id, true);
        return response(['data' => $education], 200);
    }

    public function update(KyeEducationRequest $request, $id)
    {
        $education = $this->educationService->update($id, $request->validated());
        if ($education)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->educationService->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
