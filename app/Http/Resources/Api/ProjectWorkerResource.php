<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectWorkerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'worker_id' => $this->worker_id,
            'worker' => $this->whenLoaded('worker', fn () => [
                'id' => $this->worker->id,
                'name' => $this->worker->name,
                'job_title' => $this->worker->job_title,
            ]),
            'project' => $this->whenLoaded('project', fn () => [
                'id' => $this->project->id,
                'name' => $this->project->name,
                'status' => $this->project->status,
            ]),
            'role' => $this->role,
            'date' => $this->date,
        ];
    }
}
