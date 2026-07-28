<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
