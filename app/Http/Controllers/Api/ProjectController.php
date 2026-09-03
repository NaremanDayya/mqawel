<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

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
        $record = Project::where('company_id', $request->user()->company_id)->findOrFail($project);

        return new ProjectResource($record);
    }
}
