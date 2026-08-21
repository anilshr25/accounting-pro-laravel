<?php

namespace App\Services\Tenant\Kye\Experience;

use App\Models\Tenant\Kye\Experience\KyeExperience;
use App\Http\Resources\Tenant\Kye\Experience\KyeExperienceResource;

class KyeExperienceService
{
    protected $experience;

    public function __construct(KyeExperience $experience)
    {
        $this->experience = $experience;
    }

    public function paginate($request, $limit = 25)
    {
        $experiences = $this->experience
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($sub) use ($search) {
                    $sub->where('company_name', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%");
                });
            })

            ->when($request->filled('employee_id'), function ($query) use ($request) {
                $query->where('employee_id', $request->employee_id);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->where('start_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($query) use ($request) {
                $query->where('end_date', '<=', $request->date_upto);
            })
            ->orderBy('id', 'asc')
            ->paginate($request->limit ?? $limit);

        return KyeExperienceResource::collection($experiences);
    }

    public function store($data)
    {
        try {
            return $this->experience->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $experience = $this->experience->find($id);

        if (!$experience) {
            return null;
        }

        return $resource
            ? new KyeExperienceResource($experience)
            : $experience;
    }

    public function update($id, $data)
    {
        try {
            $experience = $this->find($id);

            if (!$experience) {
                return false;
            }

            $experience->update($data);

            return $experience;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $experience = $this->find($id);

            if (!$experience) {
                return false;
            }

            return $experience->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
