<?php

namespace App\Services\Tenant\Kye\EmergencyContact;

use App\Models\Tenant\Kye\EmergencyContact\KyeEmergencyContact;
use App\Http\Resources\Tenant\Kye\EmergencyContact\KyeEmergencyContactResource;

class KyeEmergencyContactService
{
    protected $emergencyContact;

    public function __construct(KyeEmergencyContact $emergencyContact)
    {
        $this->emergencyContact = $emergencyContact;
    }

    public function paginate($request, $limit = 25)
    {
        $emergencyContacts = $this->emergencyContact
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('relationship', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })

            ->when($request->filled('employee_id'), function ($query) use ($request) {
                $query->where('employee_id', $request->employee_id);
            })
            ->orderBy('id', 'asc')
            ->paginate($request->limit ?? $limit);

        return KyeEmergencyContactResource::collection($emergencyContacts);
    }

    public function store($data)
    {
        try {
            return $this->emergencyContact->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $emergencyContact = $this->emergencyContact->find($id);

        if (!$emergencyContact) {
            return null;
        }

        return $resource
            ? new KyeEmergencyContactResource($emergencyContact)
            : $emergencyContact;
    }

    public function update($id, $data)
    {
        try {
            $emergencyContact = $this->find($id);

            if (!$emergencyContact) {
                return false;
            }

            $emergencyContact->update($data);

            return $emergencyContact;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $emergencyContact = $this->find($id);

            if (!$emergencyContact) {
                return false;
            }

            return $emergencyContact->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
