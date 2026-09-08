<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ItemCategoryResource;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = ItemCategory::where('company_id', $request->user()->company_id)
            ->withCount('items')
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ItemCategoryResource::collection($categories);
    }

    public function show(Request $request, int $category)
    {
        return new ItemCategoryResource($this->findForCompany($request, $category)->loadCount('items'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role?->can_write_item_categories, 403);

        $data = $request->validate($this->rules($request));

        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $category = ItemCategory::create($data)->refresh();

        return (new ItemCategoryResource($category->loadCount('items')))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $category)
    {
        abort_unless($request->user()->role?->can_edit_item_categories, 403);

        $record = $this->findForCompany($request, $category);

        $record->update($request->validate($this->rules($request, $record->id)));

        return new ItemCategoryResource($record->loadCount('items'));
    }

    public function destroy(Request $request, int $category)
    {
        abort_unless($request->user()->role?->can_edit_item_categories, 403);

        $this->findForCompany($request, $category)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function rules(Request $request, ?int $ignoreId = null): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('item_categories', 'name')
                    ->where('company_id', $request->user()->company_id)
                    ->ignore($ignoreId),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function findForCompany(Request $request, int $id): ItemCategory
    {
        $category = ItemCategory::findOrFail($id);

        abort_if($category->company_id !== $request->user()->company_id, 403);

        return $category;
    }
}
