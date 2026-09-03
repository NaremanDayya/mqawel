<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'parent_table' => $this->parent_table,
            'parent_id' => $this->parent_id,
            'category' => $this->category,
            'status' => $this->status,
            'issue_date' => $this->issue_date?->toDateString(),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'is_active' => (bool) $this->is_active,
            'url' => $this->file ? Storage::disk('public')->url($this->file) : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
