<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\OwnerUser\OwnerUser
 */
class OwnerUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
             'id' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image,
            'image_path' => $this->image_path,
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
            'approved_by' => $this->approved_by,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
