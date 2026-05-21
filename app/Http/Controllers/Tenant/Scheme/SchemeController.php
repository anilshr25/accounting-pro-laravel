<?php

namespace App\Http\Controllers\Tenant\Scheme;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Scheme\SchemeRequest;
use App\Services\Tenant\Scheme\SchemeService;
use App\Http\Resources\Tenant\Scheme\SchemeResource;

class SchemeController extends Controller
{
    protected $scheme;

    public function __construct(SchemeService $scheme)
    {
        $this->scheme = $scheme;
    }

    public function index(Request $request)
    {
        return $this->scheme->paginate($request, 25);
    }

    public function store(SchemeRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $scheme = $this->scheme->store($data);
        if ($scheme)
            return response([
                'status' => 'OK',
                'data' => new SchemeResource($scheme)
            ], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $scheme = $this->scheme->find($id, true);
        return response(['data' => $scheme], 200);
    }

    public function update(SchemeRequest $request, $id)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $scheme = $this->scheme->update($id, $data);

        if ($scheme) {
            return response([
                'status' => 'OK',
                'data' => new SchemeResource($scheme)
            ], 200);
        }
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->scheme->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
