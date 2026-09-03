<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'storage_id' => $this->storage_id,
            'category_id' => $this->category_id,
            'picture' => $this->picture,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'unit_price' => $this->unit_price,
            'status' => $this->status,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
