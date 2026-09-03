<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::where('company_id', $request->user()->company_id)
            ->when($request->filled('storage_id'), fn ($query) => $query->where('storage_id', $request->integer('storage_id')))
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ItemResource::collection($items);
    }

    public function show(Request $request, int $item)
    {
        $record = Item::where('company_id', $request->user()->company_id)->findOrFail($item);

        return new ItemResource($record);
    }
}
