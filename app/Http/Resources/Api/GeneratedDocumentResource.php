<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class GeneratedDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'template_id' => $this->template_id,
            'project_id' => $this->project_id,
            'project' => $this->whenLoaded('project', fn () => $this->project ? [
                'id' => $this->project->id,
                'name' => $this->project->name,
            ] : null),
            'name' => $this->name,
            'category' => $this->category,
            'related_party' => $this->related_party,
            'details' => $this->details,
            'content' => $this->content,
            'url' => $this->file ? Storage::disk('public')->url($this->file) : null,
            'status' => $this->status,
            'value' => $this->value,
            'issue_date' => $this->issue_date?->toDateString(),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
