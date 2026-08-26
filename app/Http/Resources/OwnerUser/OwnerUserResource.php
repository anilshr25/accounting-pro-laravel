<?php

declare(strict_types=1);

namespace App\Http\Resources\OwnerUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Business\BusinessResource;

class OwnerUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image,
            'company_name' => $this->company_name,
            'company_address' => $this->company_address,
            'workplace' => $this->workplace,
            'company_email' => $this->company_email,
            'company_pan_no' => $this->company_pan_no,
            'company_registration_no' => $this->company_registration_no,
            'company_industry' => $this->company_industry ?? null,
            'company_website' => $this->company_website ?? null,
            'company_country' => $this->company_country ?? null,
            'doc_one_path' => $this->doc_one_path,
            'doc_two_path' => $this->doc_two_path,
            'doc_three_path' => $this->doc_three_path,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'approved_by' => $this->approved_by,
            'remarks' => $this->remarks,
            'businesses' => BusinessResource::collection(
                $this->whenLoaded('businesses')
            ),
        ];
    }
}
