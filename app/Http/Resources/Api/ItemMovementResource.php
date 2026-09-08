<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'storage_id' => $this->storage_id,
            'project_id' => $this->project_id,
            'to_project_id' => $this->to_project_id,
            'item_id' => $this->item_id,
            'item' => $this->whenLoaded('item', fn () => [
                'id' => $this->item->id,
                'name' => $this->item->name,
            ]),
            'type' => $this->type,
            'quantity' => $this->quantity,
            'previous_storage_quantity' => $this->previous_storage_quantity,
            'new_storage_quantity' => $this->new_storage_quantity,
            'movement_date' => $this->movement_date,
            'notes' => $this->notes,
            'address' => $this->address,
        ];
    }
}
