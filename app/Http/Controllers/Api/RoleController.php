<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CompanyRoleResource;
use App\Models\CompanyRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role?->can_read_roles, 403);

        $roles = CompanyRole::where('company_id', $request->user()->company_id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return CompanyRoleResource::collection($roles);
    }

    public function show(Request $request, int $role)
    {
        abort_unless($request->user()->role?->can_read_roles, 403);

        return new CompanyRoleResource($this->findForCompany($request, $role));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role?->can_write_roles, 403);

        $data = $this->validated($request);

        $role = CompanyRole::create([
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
            'name' => $data['name'],
            ...CompanyRole::columnsFromPermissionsMap($data['permissions'] ?? []),
        ]);

        return (new CompanyRoleResource($role))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $role)
    {
        abort_unless($request->user()->role?->can_edit_roles, 403);

        $record = $this->findForCompany($request, $role);

        $data = $this->validated($request, $record->id, forUpdate: true);

        $record->update([
            ...(isset($data['name']) ? ['name' => $data['name']] : []),
            ...CompanyRole::columnsFromPermissionsMap($data['permissions'] ?? []),
        ]);

        return new CompanyRoleResource($record->refresh());
    }

    public function destroy(Request $request, int $role)
    {
        abort_unless($request->user()->role?->can_edit_roles, 403);

        $record = $this->findForCompany($request, $role);

        abort_if($record->users()->exists(), 422, __('backend.role_in_use'));

        $record->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function validated(Request $request, ?int $ignoreId = null, bool $forUpdate = false): array
    {
        return $request->validate([
            'name' => [
                $forUpdate ? 'sometimes' : 'required', 'string', 'max:255',
                Rule::unique('company_roles', 'name')
                    ->where('company_id', $request->user()->company_id)
                    ->ignore($ignoreId),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*.read' => ['nullable', 'boolean'],
            'permissions.*.write' => ['nullable', 'boolean'],
            'permissions.*.edit' => ['nullable', 'boolean'],
        ]);
    }

    private function findForCompany(Request $request, int $id): CompanyRole
    {
        $role = CompanyRole::findOrFail($id);

        abort_if($role->company_id !== $request->user()->company_id, 403);

        return $role;
    }
}
