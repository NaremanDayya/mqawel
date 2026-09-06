<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WorkerResource;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        return new WorkerResource($this->findForCompany($request, $worker));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role?->can_write_workers, 403);

        $data = $this->validated($request);

        if ($request->hasFile('picture')) {
            $data['picture'] = $request->file('picture')->store('form-attachments', 'public');
        }

        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $worker = Worker::create($data)->refresh();

        return (new WorkerResource($worker))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $worker)
    {
        abort_unless($request->user()->role?->can_edit_workers, 403);

        $record = $this->findForCompany($request, $worker);

        $data = $this->validated($request, $record->id);

        if ($request->hasFile('picture')) {
            $data['picture'] = $request->file('picture')->store('form-attachments', 'public');
        }

        $record->update($data);

        return new WorkerResource($record);
    }

    public function destroy(Request $request, int $worker)
    {
        abort_unless($request->user()->role?->can_edit_workers, 403);

        $this->findForCompany($request, $worker)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('workers', 'name')
                    ->where('company_id', $request->user()->company_id)
                    ->ignore($ignoreId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'ethnicity' => ['nullable', 'string', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:255'],
            'living_address' => ['nullable', 'string', 'max:255'],
            'living_type' => ['nullable', Rule::in(['temporary', 'primary'])],
            'job_title' => ['required', 'string', 'max:255'],
            'job_description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'picture' => ['nullable', 'image', 'max:10240'],
        ]);
    }

    private function findForCompany(Request $request, int $id): Worker
    {
        $worker = Worker::findOrFail($id);

        abort_if($worker->company_id !== $request->user()->company_id, 403);

        return $worker;
    }
}
