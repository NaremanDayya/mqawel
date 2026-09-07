<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'picture' => $this->picture,
            'id_number' => $this->id_number,
            'job_title' => $this->job_title,
            'job_description' => $this->job_description,
            'is_admin' => (bool) $this->is_admin,
            'is_active' => (bool) $this->is_active,
            'role' => $this->whenLoaded('role', fn () => $this->role ? [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ] : null),
            'role_name' => $this->whenLoaded('role', fn () => $this->role?->name),
            'permissions' => $this->whenLoaded('role', fn () => $this->role?->permissionsMap() ?? []),
            'company_id' => $this->company_id,
        ];
    }
}
