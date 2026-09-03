<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WorkerResource;
use App\Models\Worker;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function index(Request $request)
    {
        $workers = Worker::where('company_id', $request->user()->company_id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return WorkerResource::collection($workers);
    }

    public function show(Request $request, int $worker)
    {
        $record = Worker::where('company_id', $request->user()->company_id)->findOrFail($worker);

        return new WorkerResource($record);
    }
}
