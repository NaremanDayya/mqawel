<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\Item;
use App\Models\Project;
use App\Models\Storage;
use App\Models\Worker;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:2'],
        ]);

        $companyId = $request->user()->company_id;
        $term = $data['q'];
        $limit = 5;

        $workers = Worker::where('company_id', $companyId)
            ->where('name', 'like', "%{$term}%")
            ->limit($limit)
            ->get(['id', 'name', 'job_title'])
            ->map(fn (Worker $worker) => ['id' => $worker->id, 'title' => $worker->name, 'subtitle' => $worker->job_title]);

        $projects = Project::where('company_id', $companyId)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")->orWhere('number', 'like', "%{$term}%");
            })
            ->limit($limit)
            ->get(['id', 'name', 'status'])
            ->map(fn (Project $project) => ['id' => $project->id, 'title' => $project->name, 'subtitle' => $project->status]);

        $items = Item::where('company_id', $companyId)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%");
            })
            ->limit($limit)
            ->get(['id', 'name', 'code'])
            ->map(fn (Item $item) => ['id' => $item->id, 'title' => $item->name, 'subtitle' => $item->code]);

        $contractors = Contractor::where('company_id', $companyId)
            ->where('name', 'like', "%{$term}%")
            ->limit($limit)
            ->get(['id', 'name', 'type'])
            ->map(fn (Contractor $contractor) => ['id' => $contractor->id, 'title' => $contractor->name, 'subtitle' => $contractor->type]);

        $storages = Storage::where('company_id', $companyId)
            ->where('name', 'like', "%{$term}%")
            ->limit($limit)
            ->get(['id', 'name', 'address'])
            ->map(fn (Storage $storage) => ['id' => $storage->id, 'title' => $storage->name, 'subtitle' => $storage->address]);

        return response()->json([
            'data' => [
                'workers' => $workers,
                'projects' => $projects,
                'items' => $items,
                'contractors' => $contractors,
                'storages' => $storages,
            ],
        ]);
    }
}
