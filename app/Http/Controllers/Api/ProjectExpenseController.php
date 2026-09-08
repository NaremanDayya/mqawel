<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProjectExpenseResource;
use App\Models\Project;
use App\Models\ProjectExpense;
use Illuminate\Http\Request;

class ProjectExpenseController extends Controller
{
    public function index(Request $request, int $project)
    {
        $project = $this->findProject($request, $project);

        $expenses = ProjectExpense::where('project_id', $project->id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ProjectExpenseResource::collection($expenses);
    }

    public function store(Request $request, int $project)
    {
        $project = $this->findProject($request, $project);

        abort_unless($request->user()->role?->can_edit_projects, 403);

        $data = $request->validate($this->rules());

        $data['project_id'] = $project->id;
        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $expense = ProjectExpense::create($data)->refresh();

        return (new ProjectExpenseResource($expense))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $project, int $expense)
    {
        $project = $this->findProject($request, $project);

        abort_unless($request->user()->role?->can_edit_projects, 403);

        $record = $this->findExpense($project, $expense);

        $record->update($request->validate($this->rules()));

        return new ProjectExpenseResource($record);
    }

    public function destroy(Request $request, int $project, int $expense)
    {
        $project = $this->findProject($request, $project);

        abort_unless($request->user()->role?->can_edit_projects, 403);

        $this->findExpense($project, $expense)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric'],
            'currency' => ['nullable', 'string', 'max:10'],
            'date' => ['nullable', 'date'],
        ];
    }

    private function findProject(Request $request, int $id): Project
    {
        $project = Project::findOrFail($id);

        abort_if($project->company_id !== $request->user()->company_id, 403);

        return $project;
    }

    private function findExpense(Project $project, int $id): ProjectExpense
    {
        return ProjectExpense::where('project_id', $project->id)->findOrFail($id);
    }
}
