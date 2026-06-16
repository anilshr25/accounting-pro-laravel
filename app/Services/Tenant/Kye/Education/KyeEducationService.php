<?php

namespace App\Services\Tenant\Kye\Education;

use App\Models\Tenant\Kye\Education\KyeEducation;
use App\Http\Resources\Tenant\Kye\Education\KyeEducationResource;

class KyeEducationService
{
    protected $education;

    public function __construct(KyeEducation $education)
    {
        $this->education = $education;
    }

    public function paginate($request, $limit = 25)
    {
        $educations = $this->education
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($sub) use ($search) {
                    $sub->where('degree', 'like', "%{$search}%")
                        ->orWhere('university', 'like', "%{$search}%");
                });
            })

            ->when($request->filled('kye_id'), function ($query) use ($request) {
                $query->where('kye_id', $request->kye_id);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->where('from_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($query) use ($request) {
                $query->where('to_date', '<=', $request->date_upto);
            })
            ->orderBy('id', 'asc')
            ->paginate($request->limit ?? $limit);

        return KyeEducationResource::collection($educations);
    }

    public function store($data)
    {
        try {
            return $this->education->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $education = $this->education->find($id);

        if (!$education) {
            return null;
        }

        return $resource
            ? new KyeEducationResource($education)
            : $education;
    }

    public function update($id, $data)
    {
        try {
            $education = $this->find($id);

            if (!$education) {
                return false;
            }

            $education->update($data);

            return $education;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $education = $this->find($id);

            if (!$education) {
                return false;
            }

            return $education->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
