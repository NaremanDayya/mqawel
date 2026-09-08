<?php

namespace App\Http\Controllers\Api;

use App\Filament\Concerns\AppliesCompanyLetterhead;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GeneratedDocumentResource;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\Project;
use App\Services\Ai\AiRequestException;
use App\Services\Ai\DocumentDraftingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class DocumentController extends Controller
{
    use AppliesCompanyLetterhead;

    public function index(Request $request)
    {
        abort_unless($request->user()->role?->can_read_document_creator, 403);

        $companyId = $request->user()->company_id;

        $documents = GeneratedDocument::with('project')
            ->where('company_id', $companyId)
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->get('category')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->get('status')))
            ->when($request->filled('project_id'), fn ($query) => $query->where('project_id', $request->integer('project_id')))
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return GeneratedDocumentResource::collection($documents);
    }

    public function show(Request $request, int $document)
    {
        abort_unless($request->user()->role?->can_read_document_creator, 403);

        return new GeneratedDocumentResource($this->findForCompany($request, $document)->load('project'));
    }

    public function draft(Request $request, DocumentDraftingService $service)
    {
        abort_unless($request->user()->role?->can_write_document_creator, 403);

        $data = $request->validate($this->draftRules($request));

        $document = new GeneratedDocument($data);
        if (! empty($data['project_id'])) {
            $document->setRelation('project', Project::find($data['project_id']));
        }
        if (! empty($data['template_id'])) {
            $document->setRelation('template', DocumentTemplate::find($data['template_id']));
        }

        try {
            $content = $service->draft($document);
        } catch (AiRequestException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json(['data' => ['content' => $content]]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role?->can_write_document_creator, 403);

        $data = $request->validate($this->rules($request));

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('documents/'.Str::uuid(), 'public');
        }

        $data['status'] = $data['status'] ?? 'draft';
        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $document = GeneratedDocument::create($data)->refresh()->load('project');

        return (new GeneratedDocumentResource($document))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $document)
    {
        abort_unless($request->user()->role?->can_edit_document_creator, 403);

        $record = $this->findForCompany($request, $document);

        $data = $request->validate($this->rules($request));

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('documents/'.Str::uuid(), 'public');
        }

        $record->update($data);

        return new GeneratedDocumentResource($record->load('project'));
    }

    public function destroy(Request $request, int $document)
    {
        abort_unless($request->user()->role?->can_edit_document_creator, 403);

        $this->findForCompany($request, $document)->delete();

        return response()->json(['message' => 'deleted']);
    }

    public function download(Request $request, int $document)
    {
        abort_unless($request->user()->role?->can_read_document_creator, 403);

        $record = $this->findForCompany($request, $document);

        if ($record->file) {
            return Storage::disk('public')->download($record->file, $record->name.'.'.pathinfo($record->file, PATHINFO_EXTENSION));
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => storage_path('app/mpdf/tmp'),
        ]);

        if (session('current_lang') === 'ar') {
            $mpdf->SetDirectionality('rtl');
        }

        static::applyLetterhead($mpdf, $record->company?->letterhead);

        $mpdf->WriteHTML(Str::markdown((string) $record->content));

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', Destination::STRING_RETURN);
        }, $record->name.'-'.date('Y-m-d H-i').'.pdf', ['Content-Type' => 'application/pdf']);
    }

    private function draftRules(Request $request): array
    {
        $companyId = $request->user()->company_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'template_id' => ['nullable', Rule::exists('document_templates', 'id')],
            'related_party' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable', 'numeric'],
            'details' => ['nullable', 'string'],
        ];
    }

    private function rules(Request $request): array
    {
        $companyId = $request->user()->company_id;

        return [
            'template_id' => ['nullable', Rule::exists('document_templates', 'id')],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'related_party' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240'],
            'status' => ['nullable', Rule::in(['draft', 'in_review', 'sent', 'signed', 'completed'])],
            'value' => ['nullable', 'numeric'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
        ];
    }

    private function findForCompany(Request $request, int $id): GeneratedDocument
    {
        $document = GeneratedDocument::findOrFail($id);

        abort_if($document->company_id !== $request->user()->company_id, 403);

        return $document;
    }
}
