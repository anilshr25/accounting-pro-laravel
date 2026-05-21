<?php

namespace App\Services\Tenant\Attendance;

use App\Models\Tenant\Attendance\Attendance;
use App\Http\Resources\Tenant\Attendance\AttendanceResource;

class AttendanceService
{
    protected $attendance;
    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }
    public function paginate($request, $limit = 25)
    {
        $attendance = $this->attendance
            ->when($request->filled('employee_id'), function ($query) use ($request) {
                $query->where('employee_id', $request->employee_id);
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('date', $request->date);
            })
            ->when($request->filled('from_date') && $request->filled('to_date'), function ($query) use ($request) {
                $query->whereBetween('date', [$request->from_date, $request->to_date]);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest('date')
            ->paginate($request->limit ?? $limit);

        return AttendanceResource::collection($attendance);
    }

    public function search($request, $limit = 10)
    {
        $attendance = $this->attendance
            ->when($request->filled('employee_id'), function ($query) use ($request) {
                $query->where('employee_id', $request->employee_id);
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('date', $request->date);
            })
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();
        return AttendanceResource::collection($attendance);
    }

    public function store($data)
    {
        try {
            return $this->attendance->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $attendance = $this->attendance->find($id);
        if (!$attendance) {
            return null;
        }
        return $resource ? new AttendanceResource($attendance) : $attendance;
    }

    public function update($id, $data)
    {
        try {
            $attendance = $this->find($id);
            if (!$attendance) {
                return false;
            }
            return $attendance->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $attendance = $this->find($id);
            if (!$attendance) {
                return false;
            }
            return $attendance->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
