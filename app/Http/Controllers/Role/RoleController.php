<?php

namespace App\Http\Controllers\Role;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleRequest;
use App\Http\Requests\Role\AssignPermissionRequest;
use App\Services\Role\RoleService;

class RoleController extends Controller
{
    protected $role;

    public function __construct(RoleService $role)
    {
        $this->role = $role;
    }

    public function index(Request $request)
    {
        return $this->role->paginate(
            $request,
            $request->integer('limit', 25)
        );
    }

    public function store(RoleRequest $request)
    {
        $role = $this->role->store(
            $request->validated()
        );

        if ($role)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $role = $this->role->find($id, true);

        if (!$role)
            return response(['status' => 'ERROR'], 404);

        return response([
            'data' => $role
        ], 200);
    }

    public function update(RoleRequest $request, $id)
    {
        $role = $this->role->update(
            $id,
            $request->validated()
        );

        if ($role)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->role->delete($id))
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function assignPermissions(
        AssignPermissionRequest $request,
        $id
    ) {
        $role = $this->role->assignPermissions(
            $id,
            $request->validated()['permission_ids']
        );

        if ($role)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function permissions($id)
    {
        $role = $this->role->permissions($id);

        if (!$role)
            return response(['status' => 'ERROR'], 404);

        return response([
            'data' => $role
        ], 200);
    }
}
