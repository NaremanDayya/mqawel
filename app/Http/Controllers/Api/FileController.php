<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FileResource;
use App\Models\Company;
use App\Models\Contractor;
use App\Models\File;
use App\Models\Item;
use App\Models\Project;
use App\Models\Storage as StorageModel;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
    private const PARENT_TABLES = ['users', 'companies', 'workers', 'storages', 'projects', 'storage_items', 'contractors', 'items'];

    private const PARENT_MODELS = [
        'users' => User::class,
        'companies' => Company::class,
        'workers' => Worker::class,
        'storages' => StorageModel::class,
        'projects' => Project::class,
        'storage_items' => Item::class,
        'contractors' => Contractor::class,
        'items' => Item::class,
    ];

    private const PERMISSION_KEYS = [
        'workers' => 'workers',
        'projects' => 'projects',
        'contractors' => 'contractors',
        'storages' => 'storages',
        'items' => 'items',
        'storage_items' => 'items',
        'users' => 'users',
    ];

    public function index(Request $request)
    {
        $files = File::where('company_id', $request->user()->company_id)
            ->when($request->filled('parent_table'), fn ($query) => $query->where('parent_table', $request->string('parent_table')))
            ->when($request->filled('parent_id'), fn ($query) => $query->where('parent_id', $request->integer('parent_id')))
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return FileResource::collection($files);
    }

    public function show(Request $request, int $file)
    {
        return new FileResource($this->findForCompany($request, $file));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules($request, true));

        $this->authorizeParent($request, $data['parent_table'], 'write');
        $this->validateParentBelongsToCompany($request, $data['parent_table'], $data['parent_id']);

        $data['file'] = $request->file('file')->store('documents/'.Str::uuid(), 'public');
        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $record = File::create($data)->refresh();

        return (new FileResource($record))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $file)
    {
        $record = $this->findForCompany($request, $file);

        $data = $request->validate($this->rules($request, false));

        $parentTable = $data['parent_table'] ?? $record->parent_table;
        $this->authorizeParent($request, $parentTable, 'edit');

        if (isset($data['parent_table']) || isset($data['parent_id'])) {
            $this->validateParentBelongsToCompany($request, $parentTable, $data['parent_id'] ?? $record->parent_id);
        }

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('documents/'.Str::uuid(), 'public');
        }

        $record->update($data);

        return new FileResource($record);
    }

    public function destroy(Request $request, int $file)
    {
        $record = $this->findForCompany($request, $file);

        $this->authorizeParent($request, $record->parent_table, 'edit');

        $record->delete();

        return response()->json(['message' => 'deleted']);
    }

    public function download(Request $request, int $file)
    {
        $record = $this->findForCompany($request, $file);

        return Storage::disk('public')->download($record->file, $record->downloadFilename());
    }

    private function rules(Request $request, bool $isCreate): array
    {
        return [
            'parent_table' => [$isCreate ? 'required' : 'sometimes', Rule::in(self::PARENT_TABLES)],
            'parent_id' => [$isCreate ? 'required' : 'sometimes', 'integer'],
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
            'file' => [$isCreate ? 'required' : 'nullable', 'file', 'max:10240'],
            'description' => ['nullable', 'string'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'validity_type' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function authorizeParent(Request $request, string $parentTable, string $action): void
    {
        $key = self::PERMISSION_KEYS[$parentTable] ?? null;

        if ($key === null) {
            return;
        }

        $field = "can_{$action}_{$key}";

        abort_unless($request->user()->role?->{$field}, 403);
    }

    private function validateParentBelongsToCompany(Request $request, string $parentTable, int $parentId): void
    {
        $companyId = $request->user()->company_id;

        if ($parentTable === 'companies') {
            if ($parentId !== $companyId) {
                throw ValidationException::withMessages(['parent_id' => 'Invalid parent_id for this company.']);
            }

            return;
        }

        $modelClass = self::PARENT_MODELS[$parentTable];

        $exists = $modelClass::where('id', $parentId)->where('company_id', $companyId)->exists();

        if (! $exists) {
            throw ValidationException::withMessages(['parent_id' => 'Invalid parent_id for this company.']);
        }
    }

    private function findForCompany(Request $request, int $id): File
    {
        $file = File::findOrFail($id);

        abort_if($file->company_id !== $request->user()->company_id, 403);

        return $file;
    }
}
