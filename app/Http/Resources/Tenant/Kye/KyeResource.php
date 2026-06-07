<?php

namespace App\Http\Resources\Tenant\Kye;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Tenant\Kye\Address\KyeAddressResource;
use App\Http\Resources\Tenant\Kye\Education\KyeEducationResource;
use App\Http\Resources\Tenant\Kye\Experience\KyeExperienceResource;
use App\Http\Resources\Tenant\Kye\Service\KyeServiceResource;
use App\Http\Resources\Tenant\Kye\EmergencyContact\KyeEmergencyContactResource;

class KyeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'date_of_birth_ad' => $this->date_of_birth_ad ? $this->date_of_birth_ad->format('Y-m-d') : null,
            'date_of_birth_bs' => $this->date_of_birth_bs ? $this->date_of_birth_bs->format('Y-m-d') : null,
            'marital_status' => $this->marital_status,
            'gender' => $this->gender,
            'blood_group' => $this->blood_group,
            'citizenship_number' => $this->citizenship_number,
            'issue_date' => $this->issue_date ? $this->issue_date->format('Y-m-d') : null,
            'issue_district' => $this->issue_district,
            'front_image' => $this->front_image
                ? url($this->front_image)
                : null,
            'back_image' => $this->back_image
                ? url($this->back_image)
                : null,

            'addresses' => KyeAddressResource::collection(
                $this->addresses ?? collect()
            ),

            'education' => $this->educations
                ? new KyeEducationResource($this->educations)
                : null,

            'experiences' => KyeExperienceResource::collection(
                $this->experiences ?? collect()
            ),

            'services' => KyeServiceResource::collection(
                $this->services ?? collect()
            ),

            'emergency_contact' => $this->emergencyContact
                ? new KyeEmergencyContactResource($this->emergencyContact)
                : null,
        ];
    }
}
