<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProjectItemResource;
use App\Models\Project;
use App\Models\ProjectItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectItemController extends Controller
{
    public function index(Request $request, int $project)
    {
        $project = $this->findProject($request, $project);

        $items = ProjectItem::with('item')
            ->where('project_id', $project->id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ProjectItemResource::collection($items);
    }

    public function store(Request $request, int $project)
    {
        $project = $this->findProject($request, $project);

        abort_unless($request->user()->role?->can_edit_projects, 403);

        $companyId = $request->user()->company_id;

        $data = $request->validate([
            'item_id' => ['required', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'quantity' => ['required', 'numeric'],
            'date' => ['nullable', 'date'],
        ]);

        $projectItem = ProjectItem::create([
            'project_id' => $project->id,
            'item_id' => $data['item_id'],
            'quantity' => $data['quantity'],
            'date' => $data['date'] ?? null,
            'company_id' => $companyId,
            'created_by' => $request->user()->id,
        ])->refresh()->load('item');

        return (new ProjectItemResource($projectItem))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, int $project, int $item)
    {
        $project = $this->findProject($request, $project);

        abort_unless($request->user()->role?->can_edit_projects, 403);

        ProjectItem::where('project_id', $project->id)->findOrFail($item)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function findProject(Request $request, int $id): Project
    {
        $project = Project::findOrFail($id);

        abort_if($project->company_id !== $request->user()->company_id, 403);

        return $project;
    }
}
