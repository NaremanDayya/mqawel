<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'number' => $this->number,
            'description' => $this->description,
            'location' => $this->location,
            'address' => $this->address,
            'building_system' => $this->building_system,
            'phase' => $this->phase,
            'owner_name' => $this->owner_name,
            'owner_phone' => $this->owner_phone,
            'budget' => $this->budget,
            'currency' => $this->currency,
            'status' => $this->status,
            'completion_percentage' => $this->completion_percentage,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
