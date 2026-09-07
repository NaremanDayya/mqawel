<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->data['title'] ?? null,
            'body' => $this->data['body'] ?? null,
            'icon' => $this->data['icon'] ?? null,
            'icon_color' => $this->data['iconColor'] ?? null,
            'status' => $this->data['status'] ?? null,
            'is_read' => ! is_null($this->read_at),
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
