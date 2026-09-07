<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'can_read_users',
        'can_write_users',
        'can_edit_users',
        'can_read_workers',
        'can_write_workers',
        'can_edit_workers',
        'can_read_projects',
        'can_write_projects',
        'can_edit_projects',
        'can_read_storages',
        'can_write_storages',
        'can_edit_storages',
        'can_read_items',
        'can_write_items',
        'can_edit_items',
        'can_read_item_categories',
        'can_write_item_categories',
        'can_edit_item_categories',
        'can_read_item_movements',
        'can_write_item_movements',
        'can_edit_item_movements',
        'can_read_contractors',
        'can_write_contractors',
        'can_edit_contractors',
        'can_read_workers_report',
        'can_write_workers_report',
        'can_edit_workers_report',
        'can_read_worker_expenses_report',
        'can_write_worker_expenses_report',
        'can_edit_worker_expenses_report',
        'can_read_expired_files_report',
        'can_write_expired_files_report',
        'can_edit_expired_files_report',
        'can_read_project_expenses_report',
        'can_write_project_expenses_report',
        'can_edit_project_expenses_report',
        'can_read_roles',
        'can_write_roles',
        'can_edit_roles',
        'can_read_document_creator',
        'can_write_document_creator',
        'can_edit_document_creator',
        'created_by',
    ];

    public function createdBy(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): HasMany {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Resolve the can_read_ / can_write_ / can_edit_ columns into a
     * module-to-read/write/edit map for API consumers.
     */
    public function permissionsMap(): array
    {
        $map = [];

        foreach ($this->getFillable() as $column) {
            foreach (['can_read_' => 'read', 'can_write_' => 'write', 'can_edit_' => 'edit'] as $prefix => $key) {
                if (str_starts_with($column, $prefix)) {
                    $module = substr($column, strlen($prefix));
                    $map[$module][$key] = (bool) $this->{$column};
                }
            }
        }

        return $map;
    }

    /**
     * Reverse of permissionsMap(): turn a {module: {read, write, edit}}
     * array back into can_read_{module}/can_write_{module}/can_edit_{module}
     * column values, for API create/update requests.
     */
    public static function columnsFromPermissionsMap(array $permissions): array
    {
        $columns = [];

        foreach ($permissions as $module => $flags) {
            foreach (['read', 'write', 'edit'] as $key) {
                if (array_key_exists($key, $flags)) {
                    $columns['can_'.$key.'_'.$module] = (bool) $flags[$key];
                }
            }
        }

        return $columns;
    }
}
