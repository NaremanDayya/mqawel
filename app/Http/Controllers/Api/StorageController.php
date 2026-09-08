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
        return new StorageResource($this->findForCompany($request, $storage)->loadCount('items'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role?->can_write_storages, 403);

        $data = $request->validate($this->rules());

        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $storage = Storage::create($data)->refresh();

        return (new StorageResource($storage->loadCount('items')))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $storage)
    {
        abort_unless($request->user()->role?->can_edit_storages, 403);

        $record = $this->findForCompany($request, $storage);

        $record->update($request->validate($this->rules()));

        return new StorageResource($record->loadCount('items'));
    }

    public function destroy(Request $request, int $storage)
    {
        abort_unless($request->user()->role?->can_edit_storages, 403);

        $this->findForCompany($request, $storage)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function findForCompany(Request $request, int $id): Storage
    {
        $storage = Storage::findOrFail($id);

        abort_if($storage->company_id !== $request->user()->company_id, 403);

        return $storage;
    }
}
