<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Http\Requests\User\AssignBusinessRequest;
use App\Services\User\UserService;

class UserController extends Controller
{
    protected $user;

    public function __construct(UserService $user)
    {
        $this->user = $user;
    }

    public function index(Request $request)
    {
        return $this->user->paginate(
            $request,
            $request->integer('limit', 25)
        );
    }

    public function store(UserRequest $request)
    {
        $user = $this->user->store(
            $request->validated()
        );

        if ($user)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $user = $this->user->find($id, true);

        return response([
            'data' => $user
        ], 200);
    }

    public function update(UserRequest $request, $id)
    {
        $user = $this->user->update(
            $id,
            $request->validated()
        );

        if ($user)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->user->delete($id))
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function assignBusiness(
        AssignBusinessRequest $request,
        $id
    ) {
        $user = $this->user->assignBusiness(
            $id,
            $request->validated()
        );

        if ($user)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function updateBusinessAccess(
        AssignBusinessRequest $request,
        $id,
        $tenantId
    ) {
        $user = $this->user->updateBusinessAccess(
            $id,
            $tenantId,
            $request->validated()
        );

        if ($user)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function removeBusiness($id, $tenantId)
    {
        if ($this->user->removeBusiness($id, $tenantId))
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function businesses($id)
    {
        $user = $this->user->getBusinesses($id);

        if (!$user)
            return response(['status' => 'ERROR'], 404);

        return response([
            'data' => $user
        ], 200);
    }
}
