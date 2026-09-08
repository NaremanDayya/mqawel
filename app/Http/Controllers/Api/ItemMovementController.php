<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ItemMovementResource;
use App\Models\ItemMovement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = ItemMovement::with('item')->where('company_id', $request->user()->company_id);

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->integer('item_id'));
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }
        if ($request->filled('storage_id')) {
            $query->where('storage_id', $request->integer('storage_id'));
        }

        $movements = $query->latest('id')->paginate($request->integer('per_page', 20));

        return ItemMovementResource::collection($movements);
    }

    public function show(Request $request, int $movement)
    {
        return new ItemMovementResource($this->findForCompany($request, $movement)->load('item'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role?->can_write_item_movements, 403);

        $data = $request->validate($this->rules($request));

        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $movement = ItemMovement::create($data)->refresh()->load('item');

        return (new ItemMovementResource($movement))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $movement)
    {
        abort_unless($request->user()->role?->can_edit_item_movements, 403);

        $record = $this->findForCompany($request, $movement);

        $record->update($request->validate($this->rules($request)));

        return new ItemMovementResource($record->load('item'));
    }

    public function destroy(Request $request, int $movement)
    {
        abort_unless($request->user()->role?->can_edit_item_movements, 403);

        $this->findForCompany($request, $movement)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function rules(Request $request): array
    {
        $companyId = $request->user()->company_id;

        return [
            'item_id' => ['required', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'storage_id' => ['nullable', Rule::exists('storages', 'id')->where('company_id', $companyId)],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'to_project_id' => ['nullable', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'type' => ['required', Rule::in(['in', 'out', 'adjust'])],
            'quantity' => ['required', 'numeric'],
            'previous_storage_quantity' => ['nullable', 'numeric'],
            'new_storage_quantity' => ['nullable', 'numeric'],
            'movement_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function findForCompany(Request $request, int $id): ItemMovement
    {
        $movement = ItemMovement::findOrFail($id);

        abort_if($movement->company_id !== $request->user()->company_id, 403);

        return $movement;
    }
}
