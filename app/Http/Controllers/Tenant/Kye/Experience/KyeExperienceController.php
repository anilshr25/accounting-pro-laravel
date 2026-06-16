<?php

namespace App\Http\Controllers\Tenant\Kye\Experience;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Kye\Experience\KyeExperienceRequest;
use App\Services\Tenant\Kye\Experience\KyeExperienceService;
use Illuminate\Http\Request;

class KyeExperienceController extends Controller
{
    protected $experienceService;

    public function __construct(KyeExperienceService $experienceService)
    {
        $this->experienceService = $experienceService;
    }

    public function index(Request $request)
    {
        return $this->experienceService->paginate($request, 25);
    }

    public function store(KyeExperienceRequest $request)
    {
        $experience = $this->experienceService->store($request->validated());
        if ($experience)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $experience = $this->experienceService->find($id, true);
        return response(['data' => $experience], 200);
    }

    public function update(KyeExperienceRequest $request, $id)
    {
        $experience = $this->experienceService->update($id, $request->validated());
        if ($experience)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->experienceService->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
