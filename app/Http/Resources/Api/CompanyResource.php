<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'picture' => $this->picture,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'business_number' => $this->business_number,
            'tax_number' => $this->tax_number,
            'activity' => $this->activity,
            'description' => $this->description,
            'founded_year' => $this->founded_year,
            'address' => $this->address,
            'services' => $this->services,
            'is_active' => (bool) $this->is_active,
            'is_verified' => (bool) $this->is_verified,
        ];
    }
}
