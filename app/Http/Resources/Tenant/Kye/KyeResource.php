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
            'date_of_birth_ad' => $this->date_of_birth_ad->format('Y-m-d'),
            'date_of_birth_bs' => $this->date_of_birth_bs->format('Y-m-d'),
            'marital_status' => $this->marital_status,
            'gender' => $this->gender,
            'blood_group' => $this->blood_group,
            'citizenship_number' => $this->citizenship_number,
            'issue_date' => $this->issue_date->format('Y-m-d'),
            'issue_district' => $this->issue_district,

            'addresses' => KyeAddressResource::collection(
                $this->whenLoaded('addresses')
            ),

            'educations' => KyeEducationResource::collection(
                $this->whenLoaded('educations')
            ),

            'experiences' => KyeExperienceResource::collection(
                $this->whenLoaded('experiences')
            ),

            'services' => KyeServiceResource::collection(
                $this->whenLoaded('services')
            ),

            'emergency_contact' => new KyeEmergencyContactResource(
                $this->whenLoaded('emergencyContact')
            ),
        ];
    }
}