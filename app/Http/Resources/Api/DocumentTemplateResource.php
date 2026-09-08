<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DocumentTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'url' => $this->file ? Storage::disk('public')->url($this->file) : null,
            'last_used_at' => $this->last_used_at?->toIso8601String(),
        ];
    }
}
