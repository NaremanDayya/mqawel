<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DocumentTemplateResource;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class DocumentTemplateController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role?->can_read_document_creator, 403);

        $companyId = $request->user()->company_id;

        $templates = DocumentTemplate::where(function ($query) use ($companyId) {
            $query->where('company_id', $companyId)->orWhereNull('company_id');
        })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->get('category')))
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return DocumentTemplateResource::collection($templates);
    }

    public function show(Request $request, int $template)
    {
        abort_unless($request->user()->role?->can_read_document_creator, 403);

        return new DocumentTemplateResource($this->findVisible($request, $template));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role?->can_write_document_creator, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $data['file'] = $request->file('file')->store('documents/templates', 'public');
        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $template = DocumentTemplate::create($data)->refresh();

        return (new DocumentTemplateResource($template))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $template)
    {
        abort_unless($request->user()->role?->can_edit_document_creator, 403);

        $record = $this->findOwned($request, $template);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category' => ['sometimes', 'required', 'string', 'max:100'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('documents/templates', 'public');
        }

        $record->update($data);

        return new DocumentTemplateResource($record);
    }

    public function destroy(Request $request, int $template)
    {
        abort_unless($request->user()->role?->can_edit_document_creator, 403);

        $this->findOwned($request, $template)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function findVisible(Request $request, int $id): DocumentTemplate
    {
        $template = DocumentTemplate::findOrFail($id);

        abort_if(
            $template->company_id !== null && $template->company_id !== $request->user()->company_id,
            403
        );

        return $template;
    }

    private function findOwned(Request $request, int $id): DocumentTemplate
    {
        $template = DocumentTemplate::findOrFail($id);

        abort_if($template->company_id !== $request->user()->company_id, 403);

        return $template;
    }
}
