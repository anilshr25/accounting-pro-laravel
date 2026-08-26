<?php

namespace App\Http\Controllers\Permission;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\PermissionRequest;
use App\Services\Permission\PermissionService;

class PermissionController extends Controller
{
    protected $permission;

    public function __construct(PermissionService $permission)
    {
        $this->permission = $permission;
    }

    public function index(Request $request)
    {
        return $this->permission->paginate(
            $request,
            $request->integer('limit', 25)
        );
    }

    public function store(PermissionRequest $request)
    {
        $permission = $this->permission->store(
            $request->validated()
        );

        if ($permission)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $permission = $this->permission->find($id, true);

        if (!$permission)
            return response(['status' => 'ERROR'], 404);

        return response([
            'data' => $permission
        ], 200);
    }

    public function update(PermissionRequest $request, $id)
    {
        $permission = $this->permission->update(
            $id,
            $request->validated()
        );

        if ($permission)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->permission->delete($id))
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }
}
