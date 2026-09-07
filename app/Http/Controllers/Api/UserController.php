<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\CompanyRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role?->can_read_users, 403);

        $users = User::where('company_id', $request->user()->company_id)
            ->with('role')
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return UserResource::collection($users);
    }

    public function show(Request $request, int $user)
    {
        abort_unless($request->user()->role?->can_read_users, 403);

        return new UserResource($this->findForCompany($request, $user)->load('role'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role?->can_write_users, 403);

        $data = $this->validated($request);
        $companyId = $request->user()->company_id;

        $role = $this->resolveRole($companyId, $data['role_name'], $request->boolean('can_manage'), $request->user()->id);

        if ($request->hasFile('picture')) {
            $data['picture'] = $request->file('picture')->store('form-attachments', 'public');
        }

        $user = User::create([
            'company_id' => $companyId,
            'created_by' => $request->user()->id,
            'role_id' => $role->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'picture' => $data['picture'] ?? null,
            'id_number' => $data['id_number'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'job_description' => $data['job_description'] ?? null,
            'password' => bcrypt($data['password']),
            'is_active' => $data['is_active'] ?? true,
        ]);

        return (new UserResource($user->load('role')))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $user)
    {
        abort_unless($request->user()->role?->can_edit_users, 403);

        $record = $this->findForCompany($request, $user);

        $data = $this->validated($request, $record->id, forUpdate: true);

        if (isset($data['role_name'])) {
            $role = $this->resolveRole($record->company_id, $data['role_name'], $request->boolean('can_manage'), $request->user()->id);
            $data['role_id'] = $role->id;
        }
        unset($data['role_name']);

        if ($request->hasFile('picture')) {
            $data['picture'] = $request->file('picture')->store('form-attachments', 'public');
        }

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $record->update($data);

        return new UserResource($record->refresh()->load('role'));
    }

    public function destroy(Request $request, int $user)
    {
        abort_unless($request->user()->role?->can_edit_users, 403);

        $this->findForCompany($request, $user)->delete();

        return response()->json(['message' => 'deleted']);
    }

    private function resolveRole(int $companyId, string $roleName, bool $canManage, int $createdBy): CompanyRole
    {
        $role = CompanyRole::query()
            ->where('company_id', $companyId)
            ->where('name', $roleName)
            ->first();

        if ($role) {
            return $role;
        }

        $permissionColumns = collect((new CompanyRole())->getFillable())
            ->filter(fn (string $column): bool => str_starts_with($column, 'can_read_')
                || str_starts_with($column, 'can_write_')
                || str_starts_with($column, 'can_edit_'));

        $permissions = $permissionColumns->mapWithKeys(fn (string $column) => [
            $column => str_starts_with($column, 'can_read_') ? true : $canManage,
        ]);

        return CompanyRole::create([
            'company_id' => $companyId,
            'name' => $roleName,
            'created_by' => $createdBy,
            ...$permissions->toArray(),
        ]);
    }

    private function validated(Request $request, ?int $ignoreId = null, bool $forUpdate = false): array
    {
        $rules = [
            'name' => [$forUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'email' => [
                $forUpdate ? 'sometimes' : 'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($ignoreId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'picture' => ['nullable', 'image', 'max:10240'],
            'id_number' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'job_description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'role_name' => [$forUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'can_manage' => ['nullable', 'boolean'],
            'password' => [$forUpdate ? 'sometimes' : 'required', 'string', 'min:8'],
        ];

        return $request->validate($rules);
    }

    private function findForCompany(Request $request, int $id): User
    {
        $user = User::findOrFail($id);

        abort_if($user->company_id !== $request->user()->company_id, 403);

        return $user;
    }
}
