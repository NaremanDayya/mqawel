<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'picture' => $this->picture,
            'phone' => $this->phone,
            'ethnicity' => $this->ethnicity,
            'id_number' => $this->id_number,
            'living_address' => $this->living_address,
            'living_type' => $this->living_type,
            'job_title' => $this->job_title,
            'job_description' => $this->job_description,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
