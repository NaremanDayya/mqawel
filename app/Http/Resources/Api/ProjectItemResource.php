<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'item_id' => $this->item_id,
            'item' => $this->whenLoaded('item', fn () => $this->item ? [
                'id' => $this->item->id,
                'name' => $this->item->name,
            ] : null),
            'quantity' => $this->quantity,
            'date' => $this->date,
        ];
    }
}
