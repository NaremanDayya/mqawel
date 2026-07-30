<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemMovement extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'storage_id',
        'project_id',
        'to_project_id',
        'item_id',
        'type',
        'quantity',
        'previous_storage_quantity',
        'new_storage_quantity',
        'movement_date',
        'notes',
        'address',
        'created_by',
    ];

    public function company() : BelongsTo {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function storage() : BelongsTo {
        return $this->belongsTo(Storage::class, 'storage_id');
    }

    public function project() : BelongsTo {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function toProject() : BelongsTo {
        return $this->belongsTo(Project::class, 'to_project_id');
    }

    public function item() : BelongsTo {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function createdBy() : BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }
}
