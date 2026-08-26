<?php

namespace App\Http\Controllers\OwnerUser;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\OwnerUser\OwnerUserRequest;
use App\Services\OwnerUser\OwnerUserService;
use Illuminate\Support\Facades\Auth;

class OwnerUserController extends Controller
{
    protected $ownerUser;

    public function __construct(OwnerUserService $ownerUser)
    {
        $this->ownerUser = $ownerUser;
    }

    public function index(Request $request)
    {
        return $this->ownerUser->paginate(
            $request->integer('limit', 25),
            $request
        );
    }

    public function store(OwnerUserRequest $request)
    {
        $ownerUser = $this->ownerUser->store(
            $request->validated()
        );

        if ($ownerUser)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $ownerUser = $this->ownerUser->find($id, true);

        return response([
            'data' => $ownerUser
        ], 200);
    }

    public function update(OwnerUserRequest $request, $id)
    {
        $ownerUser = $this->ownerUser->update(
            $id,
            $request->validated()
        );

        if ($ownerUser)
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->ownerUser->delete($id))
            return response(['status' => 'OK'], 200);

        return response(['status' => 'ERROR'], 500);
    }

}
