<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::where('company_id', $request->user()->company_id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ProjectResource::collection($projects);
    }

    public function show(Request $request, int $project)
    {
        return new ProjectResource($this->findForCompany($request, $project));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role?->can_write_projects, 403);

        $data = $request->validate($this->rules());

        if ($request->hasFile('photos')) {
            $directory = 'documents/'.\Illuminate\Support\Str::uuid();
            $data['photos'] = collect($request->file('photos'))
                ->map(fn ($photo) => $photo->store($directory, 'public'))
                ->all();
        }

        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $project = Project::create($data)->refresh();

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $project)
    {
        abort_unless($request->user()->role?->can_edit_projects, 403);

        $record = $this->findForCompany($request, $project);

        $data = $request->validate($this->rules());

        if ($request->hasFile('photos')) {
            $directory = 'documents/'.\Illuminate\Support\Str::uuid();
            $data['photos'] = collect($request->file('photos'))
                ->map(fn ($photo) => $photo->store($directory, 'public'))
                ->all();
        }

        $record->update($data);

        return new ProjectResource($record);
    }

    public function destroy(Request $request, int $project)
    {
        abort_unless($request->user()->role?->can_edit_projects, 403);

        $this->findForCompany($request, $project)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'building_system' => ['nullable', 'string', 'max:255'],
            'phase' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'budget' => ['nullable', 'numeric'],
            'currency' => ['nullable', 'string', 'max:10'],
            'status' => ['required', Rule::in(['pending', 'processing', 'completed', 'cancelled'])],
            'completion_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:10240'],
        ];
    }

    private function findForCompany(Request $request, int $id): Project
    {
        $project = Project::findOrFail($id);

        abort_if($project->company_id !== $request->user()->company_id, 403);

        return $project;
    }
}
