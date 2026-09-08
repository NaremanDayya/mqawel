<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ContractorResource;
use App\Models\Contractor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContractorController extends Controller
{
    public function index(Request $request)
    {
        $contractors = Contractor::where('company_id', $request->user()->company_id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ContractorResource::collection($contractors);
    }

    public function show(Request $request, int $contractor)
    {
        return new ContractorResource($this->findForCompany($request, $contractor));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role?->can_write_contractors, 403);

        $data = $request->validate($this->rules());

        $data['company_id'] = $request->user()->company_id;
        $data['created_by'] = $request->user()->id;

        $contractor = Contractor::create($data)->refresh();

        return (new ContractorResource($contractor))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $contractor)
    {
        abort_unless($request->user()->role?->can_edit_contractors, 403);

        $record = $this->findForCompany($request, $contractor);

        $record->update($request->validate($this->rules()));

        return new ContractorResource($record);
    }

    public function destroy(Request $request, int $contractor)
    {
        abort_unless($request->user()->role?->can_edit_contractors, 403);

        $this->findForCompany($request, $contractor)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'fax' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'registry_number' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'fields' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['nullable', 'numeric'],
            'number_of_projects' => ['nullable', 'integer'],
            'areas' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function findForCompany(Request $request, int $id): Contractor
    {
        $contractor = Contractor::findOrFail($id);

        abort_if($contractor->company_id !== $request->user()->company_id, 403);

        return $contractor;
    }
}
