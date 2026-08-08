<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'storage_id',
        'name',
        'number',
        'description',
        'location',
        'address',
        'building_system',
        'phase',
        'owner_name',
        'owner_phone',
        'photos',
        'budget',
        'currency',
        'status',
        'completion_percentage',
        'created_by',
    ];

    protected $casts = [
        'photos' => 'array',
    ];

    public function company() : BelongsTo {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function storage() : BelongsTo {
        return $this->belongsTo(Storage::class, 'storage_id');
    }

    public function expenses() : HasMany {
        return $this->hasMany(ProjectExpense::class, 'project_id');
    }

    public function storages() : HasMany {
        return $this->hasMany(ProjectStorage::class);
    }

    public function users() : HasMany {
        return $this->hasMany(ProjectUser::class);
    }

    public function workers() : HasMany {
        return $this->hasMany(ProjectWorker::class);
    }

    /*public function items() : BelongsToMany {
        return $this->belongsToMany(Item::class, 'project_items')->withPivot('quantity');
    }*/

    public function items(): HasMany {
        return $this->hasMany(Item::class, 'project_id');
    }

    public function files() : HasMany {
        return $this->hasMany(File::class, 'parent_id')->where('parent_table', '=', 'projects');
    }
}
