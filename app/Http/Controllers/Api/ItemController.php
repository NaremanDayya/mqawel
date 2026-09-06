<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        return new ItemResource($this->findForCompany($request, $item));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role?->can_write_items, 403);

        $data = $request->validate($this->rules($request));

        if ($request->hasFile('picture')) {
            $data['picture'] = $request->file('picture')->store('form-attachments', 'public');
        }

        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $item = Item::create($data)->refresh();

        return (new ItemResource($item))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $item)
    {
        abort_unless($request->user()->role?->can_edit_items, 403);

        $record = $this->findForCompany($request, $item);

        $data = $request->validate($this->rules($request));

        if ($request->hasFile('picture')) {
            $data['picture'] = $request->file('picture')->store('form-attachments', 'public');
        }

        $record->update($data);

        return new ItemResource($record);
    }

    public function destroy(Request $request, int $item)
    {
        abort_unless($request->user()->role?->can_edit_items, 403);

        $this->findForCompany($request, $item)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function rules(Request $request): array
    {
        $companyId = $request->user()->company_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['required', 'numeric'],
            'unit' => ['nullable', 'string', 'max:50'],
            'unit_price' => ['nullable', 'numeric'],
            'status' => ['required', Rule::in(['new', 'used', 'damaged'])],
            'category_id' => ['nullable', Rule::exists('item_categories', 'id')->where('company_id', $companyId)],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'is_transferred' => ['nullable', 'boolean'],
            'transferred_from' => ['nullable', 'string', 'max:255'],
            'transfer_or_purchase_date' => ['nullable', 'date'],
            'performed_by' => ['nullable', 'string', 'max:255'],
            'usage_purpose' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'picture' => ['nullable', 'image', 'max:10240'],
        ];
    }

    private function findForCompany(Request $request, int $id): Item
    {
        $item = Item::findOrFail($id);

        abort_if($item->company_id !== $request->user()->company_id, 403);

        return $item;
    }
}
