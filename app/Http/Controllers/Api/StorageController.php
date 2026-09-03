<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\StorageResource;
use App\Models\Storage;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function index(Request $request)
    {
        $storages = Storage::where('company_id', $request->user()->company_id)
            ->withCount('items')
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return StorageResource::collection($storages);
    }

    public function show(Request $request, int $storage)
    {
        $record = Storage::where('company_id', $request->user()->company_id)
            ->withCount('items')
            ->findOrFail($storage);

        return new StorageResource($record);
    }
}
