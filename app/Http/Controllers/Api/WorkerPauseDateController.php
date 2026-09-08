<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WorkerPauseDateResource;
use App\Models\Worker;
use App\Models\WorkerPauseDate;
use Illuminate\Http\Request;

class WorkerPauseDateController extends Controller
{
    public function index(Request $request, int $worker)
    {
        $worker = $this->findWorker($request, $worker);

        $pauses = WorkerPauseDate::where('worker_id', $worker->id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return WorkerPauseDateResource::collection($pauses);
    }

    public function store(Request $request, int $worker)
    {
        $worker = $this->findWorker($request, $worker);

        abort_unless($request->user()->role?->can_edit_workers, 403);

        $data = $request->validate([
            'date_of_pause' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $data['worker_id'] = $worker->id;
        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $record = WorkerPauseDate::create($data)->refresh();

        return (new WorkerPauseDateResource($record))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, int $worker, int $pause)
    {
        $worker = $this->findWorker($request, $worker);

        abort_unless($request->user()->role?->can_edit_workers, 403);

        WorkerPauseDate::where('worker_id', $worker->id)->findOrFail($pause)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function findWorker(Request $request, int $id): Worker
    {
        $worker = Worker::findOrFail($id);

        abort_if($worker->company_id !== $request->user()->company_id, 403);

        return $worker;
    }
}
