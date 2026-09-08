<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WorkerWorkDayResource;
use App\Models\Worker;
use App\Models\WorkerWorkDay;
use Illuminate\Http\Request;

class WorkerWorkDayController extends Controller
{
    private const DAYS = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

    public function index(Request $request, int $worker)
    {
        $worker = $this->findWorker($request, $worker);

        $days = WorkerWorkDay::where('worker_id', $worker->id)
            ->orderByDesc('date')
            ->paginate($request->integer('per_page', 20));

        return WorkerWorkDayResource::collection($days);
    }

    public function store(Request $request, int $worker)
    {
        $worker = $this->findWorker($request, $worker);

        abort_unless($request->user()->role?->can_edit_workers, 403);

        $data = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $day = self::DAYS[\Carbon\Carbon::parse($data['date'])->dayOfWeek];

        $record = WorkerWorkDay::create([
            'worker_id' => $worker->id,
            'date' => $data['date'],
            'day' => $day,
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
        ])->refresh();

        return (new WorkerWorkDayResource($record))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, int $worker, int $day)
    {
        $worker = $this->findWorker($request, $worker);

        abort_unless($request->user()->role?->can_edit_workers, 403);

        WorkerWorkDay::where('worker_id', $worker->id)->findOrFail($day)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function findWorker(Request $request, int $id): Worker
    {
        $worker = Worker::findOrFail($id);

        abort_if($worker->company_id !== $request->user()->company_id, 403);

        return $worker;
    }
}
