<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ItemDamageResource;
use App\Models\ItemDamage;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkerDamageController extends Controller
{
    public function index(Request $request, int $worker)
    {
        $worker = $this->findWorker($request, $worker);

        $damages = ItemDamage::with('item')
            ->where('responsible_id', $worker->id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ItemDamageResource::collection($damages);
    }

    public function store(Request $request, int $worker)
    {
        $worker = $this->findWorker($request, $worker);

        abort_unless($request->user()->role?->can_edit_workers, 403);

        $data = $request->validate($this->rules($request));

        $data['responsible_id'] = $worker->id;
        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $damage = ItemDamage::create($data)->refresh()->load('item');

        return (new ItemDamageResource($damage))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $worker, int $damage)
    {
        $worker = $this->findWorker($request, $worker);

        abort_unless($request->user()->role?->can_edit_workers, 403);

        $record = $this->findDamage($worker, $damage);

        $record->update($request->validate($this->rules($request)));

        return new ItemDamageResource($record->load('item'));
    }

    public function destroy(Request $request, int $worker, int $damage)
    {
        $worker = $this->findWorker($request, $worker);

        abort_unless($request->user()->role?->can_edit_workers, 403);

        $this->findDamage($worker, $damage)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function rules(Request $request): array
    {
        $companyId = $request->user()->company_id;

        return [
            'item_id' => ['required', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'storage_id' => ['nullable', Rule::exists('storages', 'id')->where('company_id', $companyId)],
            'quantity' => ['nullable', 'numeric'],
            'damage_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function findWorker(Request $request, int $id): Worker
    {
        $worker = Worker::findOrFail($id);

        abort_if($worker->company_id !== $request->user()->company_id, 403);

        return $worker;
    }

    private function findDamage(Worker $worker, int $id): ItemDamage
    {
        return ItemDamage::where('responsible_id', $worker->id)->findOrFail($id);
    }
}
