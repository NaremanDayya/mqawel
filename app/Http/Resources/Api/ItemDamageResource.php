<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemDamageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'storage_id' => $this->storage_id,
            'item_id' => $this->item_id,
            'item' => $this->whenLoaded('item', fn () => [
                'id' => $this->item->id,
                'name' => $this->item->name,
            ]),
            'responsible_id' => $this->responsible_id,
            'responsible' => $this->whenLoaded('responsible', fn () => $this->responsible ? [
                'id' => $this->responsible->id,
                'name' => $this->responsible->name,
            ] : null),
            'quantity' => $this->quantity,
            'damage_date' => $this->damage_date,
            'reason' => $this->reason,
            'location' => $this->location,
            'notes' => $this->notes,
        ];
    }
}
