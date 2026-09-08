<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProjectWorkerResource;
use App\Models\Project;
use App\Models\ProjectWorker;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectWorkerController extends Controller
{
    public function index(Request $request, int $project)
    {
        $project = $this->findProject($request, $project);

        $workers = ProjectWorker::with('worker')
            ->where('project_id', $project->id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ProjectWorkerResource::collection($workers);
    }

    public function store(Request $request, int $project)
    {
        $project = $this->findProject($request, $project);

        abort_unless($request->user()->role?->can_edit_projects, 403);

        $companyId = $request->user()->company_id;

        $data = $request->validate([
            'worker_id' => [
                'required',
                Rule::exists('workers', 'id')->where('company_id', $companyId),
                Rule::unique('project_workers', 'worker_id')->where('project_id', $project->id),
            ],
            'role' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
        ]);

        $data['project_id'] = $project->id;
        $data['company_id'] = $companyId;
        $data['created_by'] = $request->user()->id;

        $projectWorker = ProjectWorker::create($data)->refresh()->load('worker');

        return (new ProjectWorkerResource($projectWorker))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, int $project, int $worker)
    {
        $project = $this->findProject($request, $project);

        abort_unless($request->user()->role?->can_edit_projects, 403);

        ProjectWorker::where('project_id', $project->id)->findOrFail($worker)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function findProject(Request $request, int $id): Project
    {
        $project = Project::findOrFail($id);

        abort_if($project->company_id !== $request->user()->company_id, 403);

        return $project;
    }
}
