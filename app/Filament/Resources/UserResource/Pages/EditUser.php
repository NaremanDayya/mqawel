<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\CompanyRole;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role_name'] = $this->record->role?->name;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $roleName = trim($data['role_name'] ?? '');
        unset($data['role_name']);

        if (filled($roleName)) {
            $companyId = Auth::user()->company_id;

            $role = CompanyRole::query()
                ->where('company_id', $companyId)
                ->where('name', $roleName)
                ->first();

            if (! $role) {
                $permissionColumns = collect((new CompanyRole())->getFillable())
                    ->filter(fn (string $column): bool => str_starts_with($column, 'can_read_')
                        || str_starts_with($column, 'can_write_')
                        || str_starts_with($column, 'can_edit_'));

                $permissions = $permissionColumns->mapWithKeys(fn (string $column) => [
                    $column => str_starts_with($column, 'can_read_'),
                ]);

                $role = CompanyRole::create([
                    'company_id' => $companyId,
                    'name' => $roleName,
                    'created_by' => Auth::user()->id,
                    ...$permissions->toArray(),
                ]);
            }

            $data['role_id'] = $role->id;
        }

        return $data;
    }
}
