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
        $kye = $this->service->store(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'KYE created successfully',
            'data' => new KyeResource($kye),
        ], 201);
    }

    public function show(int $id)
    {
        $kye = $this->service->findById($id);

        return response()->json([
            'success' => true,
            'message' => 'KYE retrieved successfully',
            'data' => new KyeResource($kye),
        ]);
    }

    public function update(
        KyeRequest $request,
        int $id
    ) {
        $kye = $this->service->update(
            $id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'KYE updated successfully',
            'data' => new KyeResource($kye),
        ]);
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'KYE deleted successfully',
        ]);
    }
}
