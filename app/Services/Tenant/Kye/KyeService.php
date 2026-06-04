<?php

namespace App\Services\Tenant\Kye;

use App\Models\Tenant\Kye\Kye;
use App\Http\Resources\Tenant\Kye\KyeResource;
use Illuminate\Support\Facades\DB;

class KyeService
{
    protected $kye;

    public function __construct(Kye $kye)
    {
        $this->kye = $kye;
    }

    public function paginate($request, $limit = 25)
    {
        $kye = $this->kye
            ->with([
                'addresses',
                'educations',
                'experiences',
                'services',
                'emergencyContact',
            ])

            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {

                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('citizenship_number', 'like', "%{$search}%")
                        ->orWhere('issue_district', 'like', "%{$search}%")
                        ->orWhere('marital_status', 'like', "%{$search}%")
                        ->orWhere('gender', 'like', "%{$search}%")
                        ->orWhere('blood_group', 'like', "%{$search}%");

                    $q->orWhereHas('addresses', function ($address) use ($search) {
                        $address->where('district', 'like', "%{$search}%")
                            ->orWhere('municipality', 'like', "%{$search}%")
                            ->orWhere('locality', 'like', "%{$search}%")
                            ->orWhere('ward_number', 'like', "%{$search}%");
                    });

                    $q->orWhereHas('educations', function ($education) use ($search) {
                        $education->where('degree', 'like', "%{$search}%")
                            ->orWhere('university', 'like', "%{$search}%");
                    });

                    $q->orWhereHas('experiences', function ($experience) use ($search) {
                        $experience->where('company_name', 'like', "%{$search}%")
                            ->orWhere('position', 'like', "%{$search}%");
                    });

                    $q->orWhereHas('services', function ($service) use ($search) {
                        $service->where('department', 'like', "%{$search}%")
                            ->orWhere('position', 'like', "%{$search}%")
                            ->orWhere('shift', 'like', "%{$search}%");

                        if (is_numeric($search)) {
                            $service->orWhere('salary', $search);
                        }
                    });

                    $q->orWhereHas('emergencyContact', function ($contact) use ($search) {
                        $contact->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('relationship', 'like', "%{$search}%");
                    });
                });
            })

            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('date_of_birth_ad', '>=', $request->date_from);
            })

            ->when($request->filled('date_upto'), function ($query) use ($request) {
                $query->whereDate('date_of_birth_ad', '<=', $request->date_upto);
            })

            ->when($request->filled('miti_from'), function ($query) use ($request) {
                $query->where('date_of_birth_bs', '>=', $request->miti_from);
            })

            ->when($request->filled('miti_upto'), function ($query) use ($request) {
                $query->where('date_of_birth_bs', '<=', $request->miti_upto);
            })

            ->when($request->filled('date_of_birth_ad'), function ($query) use ($request) {
                $query->whereDate('date_of_birth_ad', $request->date_of_birth_ad);
            })

            ->when($request->filled('date_of_birth_bs'), function ($query) use ($request) {
                $query->where('date_of_birth_bs', $request->date_of_birth_bs);
            })
            ->latest()
            ->paginate($request->limit ?? $limit);

        return KyeResource::collection($kye);
    }

    public function findById(int $id)
    {
        return $this->kye
            ->with([
                'addresses',
                'educations',
                'experiences',
                'services',
                'emergencyContact',
            ])
            ->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $kye = $this->kye->create([
                'full_name' => $data['full_name'],
                'date_of_birth_ad' => $data['date_of_birth_ad'],
                'date_of_birth_bs' => $data['date_of_birth_bs'],
                'marital_status' => $data['marital_status'],
                'gender' => $data['gender'],
                'blood_group' => $data['blood_group'] ?? null,
                'citizenship_number' => $data['citizenship_number'],
                'issue_date' => $data['issue_date'],
                'issue_district' => $data['issue_district'],
            ]);

            if (!empty($data['addresses'])) {
                $kye->addresses()->createMany($data['addresses']);
            }

            if (!empty($data['educations'])) {
                $kye->educations()->createMany($data['educations']);
            }

            if (!empty($data['experiences'])) {
                $kye->experiences()->createMany($data['experiences']);
            }

            if (!empty($data['services'])) {
                $kye->services()->createMany($data['services']);
            }

            if (!empty($data['emergency_contact'])) {
                $kye->emergencyContact()->create(
                    $data['emergency_contact']
                );
            }

            return $kye->load([
                'addresses',
                'educations',
                'experiences',
                'services',
                'emergencyContact',
            ]);
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {

            $kye = $this->findById($id);

            $kye->update([
                'full_name' => $data['full_name'],
                'date_of_birth_ad' => $data['date_of_birth_ad'],
                'date_of_birth_bs' => $data['date_of_birth_bs'],
                'marital_status' => $data['marital_status'],
                'gender' => $data['gender'],
                'blood_group' => $data['blood_group'] ?? null,
                'citizenship_number' => $data['citizenship_number'],
                'issue_date' => $data['issue_date'],
                'issue_district' => $data['issue_district'],
            ]);

            $kye->addresses()->delete();
            $kye->educations()->delete();
            $kye->experiences()->delete();
            $kye->services()->delete();
            $kye->emergencyContact()?->delete();

            if (!empty($data['addresses'])) {
                $kye->addresses()->createMany($data['addresses']);
            }

            if (!empty($data['educations'])) {
                $kye->educations()->createMany($data['educations']);
            }

            if (!empty($data['experiences'])) {
                $kye->experiences()->createMany($data['experiences']);
            }

            if (!empty($data['services'])) {
                $kye->services()->createMany($data['services']);
            }

            if (!empty($data['emergency_contact'])) {
                $kye->emergencyContact()->create(
                    $data['emergency_contact']
                );
            }

            return $kye->load([
                'addresses',
                'educations',
                'experiences',
                'services',
                'emergencyContact',
            ]);
        });
    }

    public function delete(int $id)
    {
        $kye = $this->findById($id);

        return $kye->delete();
    }
}
