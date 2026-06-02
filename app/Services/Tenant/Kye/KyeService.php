<?php

namespace App\Services\Tenant\Kye;

use App\Models\Tenant\Kye\Kye;
use Illuminate\Support\Facades\DB;

class KyeService
{
    protected $kye;

    public function __construct(Kye $kye)
    {
        $this->kye = $kye;
    }

    public function paginate($perPage = 10)
    {
        return $this->kye
            ->with([
                'addresses',
                'educations',
                'experiences',
                'services',
                'emergencyContact',
            ])
            ->latest()
            ->paginate($perPage);
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
