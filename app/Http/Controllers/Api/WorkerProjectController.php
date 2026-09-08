<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProjectWorkerResource;
use App\Models\ProjectWorker;
use App\Models\Worker;
use Illuminate\Http\Request;

class WorkerProjectController extends Controller
{
    public function index(Request $request, int $worker)
    {
        $worker = $this->findWorker($request, $worker);

        $projects = ProjectWorker::with('project')
            ->where('worker_id', $worker->id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ProjectWorkerResource::collection($projects);
    }

    private function findWorker(Request $request, int $id): Worker
    {
        $worker = Worker::findOrFail($id);

        abort_if($worker->company_id !== $request->user()->company_id, 403);

        return $worker;
    }
}
