<?php

namespace App\Http\Controllers\Tenant\Attendance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Attendance\AttendanceRequest;
use App\Services\Tenant\Attendance\AttendanceService;

class AttendanceController extends Controller
{
    protected $attendance;

    public function __construct(AttendanceService $attendance)
    {
        $this->attendance = $attendance;
    }

    public function index(Request $request)
    {
        return $this->attendance->paginate($request, 25);
    }

    public function search(Request $request)
    {
        return $this->attendance->search($request, 10);
    }

    public function store(AttendanceRequest $request)
    {
        $attendance = $this->attendance->store($request->validated());
        if ($attendance)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $attendance = $this->attendance->find($id, true);
        return response(['data' => $attendance], 200);
    }

    public function update(AttendanceRequest $request, $id)
    {
        $attendance = $this->attendance->update($id, $request->validated());
        if ($attendance)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->attendance->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
