<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WorkerPaymentResource;
use App\Models\Worker;
use App\Models\WorkerPayment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkerPaymentController extends Controller
{
    public function index(Request $request, int $worker)
    {
        $worker = $this->findWorker($request, $worker);

        $payments = WorkerPayment::where('worker_id', $worker->id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return WorkerPaymentResource::collection($payments);
    }

    public function show(Request $request, int $worker, int $payment)
    {
        $worker = $this->findWorker($request, $worker);

        return new WorkerPaymentResource($this->findPayment($worker, $payment));
    }

    public function store(Request $request, int $worker)
    {
        $worker = $this->findWorker($request, $worker);

        abort_unless($request->user()->role?->can_edit_workers, 403);

        $data = $request->validate($this->rules());

        $data['worker_id'] = $worker->id;
        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $payment = WorkerPayment::create($data)->refresh();

        return (new WorkerPaymentResource($payment))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $worker, int $payment)
    {
        $worker = $this->findWorker($request, $worker);

        abort_unless($request->user()->role?->can_edit_workers, 403);

        $record = $this->findPayment($worker, $payment);

        $record->update($request->validate($this->rules()));

        return new WorkerPaymentResource($record);
    }

    public function destroy(Request $request, int $worker, int $payment)
    {
        $worker = $this->findWorker($request, $worker);

        abort_unless($request->user()->role?->can_edit_workers, 403);

        $this->findPayment($worker, $payment)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function rules(): array
    {
        return [
            'payment_type' => ['required', Rule::in(['salary', 'dues'])],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'currency' => ['nullable', 'string', 'max:10'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', Rule::in(['cash', 'bank_transfer', 'cheque'])],
            'status' => ['nullable', Rule::in(['paid', 'unpaid'])],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function findWorker(Request $request, int $id): Worker
    {
        $worker = Worker::findOrFail($id);

        abort_if($worker->company_id !== $request->user()->company_id, 403);

        return $worker;
    }

    private function findPayment(Worker $worker, int $id): WorkerPayment
    {
        return WorkerPayment::where('worker_id', $worker->id)->findOrFail($id);
    }
}
