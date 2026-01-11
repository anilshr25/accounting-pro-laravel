<?php

namespace App\Http\Controllers\Tenant\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Tenant\User\UserService;
use App\Http\Requests\Tenant\User\UserRequest;

class UserController extends Controller
{
    protected $member;

    public function __construct(UserService $member)
    {
        $this->member = $member;
    }

    public function index(Request $request)
    {
        return $this->member->paginate( $request, $request->limit ?? 25 );
    }

    public function store(UserRequest $request)
    {
        $member = $this->member->store($request->validated());
        if ($member) {
            return response(['status' => "OK"], 200);
        }
        return response(['status' => "ERROR"], 500);
    }

    public function show($id)
    {
        $member = $this->member->find($id, true);
        return response(['data' => $member], 200);
    }

    public function update(UserRequest $request, $id)
    {
        $member = $this->member->update($request->validated(), $id);
        if ($member)
            return response(['status' => "OK"], 200);
        return response(['status' => "ERROR"], 500);
    }

    public function destroy($id)
    {
        if ($this->member->delete($id))
            return response(['status' => "OK"], 200);
        return response(['status' => "ERROR"], 500);
    }

}
