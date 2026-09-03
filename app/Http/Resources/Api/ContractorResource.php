<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'phone' => $this->phone,
            'email' => $this->email,
            'fax' => $this->fax,
            'website' => $this->website,
            'address' => $this->address,
            'registry_number' => $this->registry_number,
            'tax_number' => $this->tax_number,
            'category' => $this->category,
            'fields' => $this->fields,
            'years_of_experience' => $this->years_of_experience,
            'number_of_projects' => $this->number_of_projects,
            'areas' => $this->areas,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
