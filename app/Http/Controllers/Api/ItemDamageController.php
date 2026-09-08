<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ItemDamageResource;
use App\Models\Item;
use App\Models\ItemDamage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemDamageController extends Controller
{
    public function index(Request $request, int $item)
    {
        $item = $this->findItem($request, $item);

        $damages = ItemDamage::with('responsible')
            ->where('item_id', $item->id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ItemDamageResource::collection($damages);
    }

    public function store(Request $request, int $item)
    {
        $item = $this->findItem($request, $item);

        abort_unless($request->user()->role?->can_edit_items, 403);

        $data = $request->validate($this->rules($request));

        $data['item_id'] = $item->id;
        $data['storage_id'] = $data['storage_id'] ?? $item->storage_id;
        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $damage = ItemDamage::create($data)->refresh()->load('responsible');

        return (new ItemDamageResource($damage))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $item, int $damage)
    {
        $item = $this->findItem($request, $item);

        abort_unless($request->user()->role?->can_edit_items, 403);

        $record = $this->findDamage($item, $damage);

        $record->update($request->validate($this->rules($request)));

        return new ItemDamageResource($record->load('responsible'));
    }

    public function destroy(Request $request, int $item, int $damage)
    {
        $item = $this->findItem($request, $item);

        abort_unless($request->user()->role?->can_edit_items, 403);

        $this->findDamage($item, $damage)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function rules(Request $request): array
    {
        $companyId = $request->user()->company_id;

        return [
            'responsible_id' => ['nullable', Rule::exists('workers', 'id')->where('company_id', $companyId)],
            'storage_id' => ['nullable', Rule::exists('storages', 'id')->where('company_id', $companyId)],
            'quantity' => ['nullable', 'numeric'],
            'damage_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function findItem(Request $request, int $id): Item
    {
        $item = Item::findOrFail($id);

        abort_if($item->company_id !== $request->user()->company_id, 403);

        return $item;
    }

    private function findDamage(Item $item, int $id): ItemDamage
    {
        return ItemDamage::where('item_id', $item->id)->findOrFail($id);
    }
}
