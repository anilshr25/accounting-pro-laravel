<?php

namespace App\Http\Controllers\Admin\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Business\BusinessRequest;
use App\Services\Admin\Business\BusinessService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    protected $business;

    public function __construct(BusinessService $business)
    {
        $this->business = $business;
    }

    public function index(Request $request)
    {
        return $this->business->paginate($request, 25);
    }

    public function store(BusinessRequest $request)
    {
        try {
            $business = $this->business->store($request->validated());

            if (! $business) {
                return response([
                    'status' => 'ERROR',
                    'message' => 'Business could not be created.',
                ], 422);
            }

            return response([
                'status' => 'OK',
                'message' => 'Business created successfully.',
                'data' => $business,
            ], 201);
        } catch (\Throwable $ex) {
            Log::error('Business creation failed', [
                'message' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString(),
            ]);

            return response([
                'status' => 'ERROR',
                'message' => 'Something went wrong while creating the business.',
            ], 500);
        }
    }

    public function show($id)
    {
        $business = $this->business->find($id, true);
        return response(['data' => $business], 200);
    }

    public function update(BusinessRequest $request, $id)
    {
        $business = $this->business->update($id, $request->validated());
        if ($business)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->business->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
