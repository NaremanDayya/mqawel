<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worker extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'picture',
        'name',
        'phone',
        'ethnicity',
        'id_number',
        'living_address',
        'living_type',
        'job_title',
        'job_description',
        'is_active',
        'created_by',
    ];

    public function company() : BelongsTo {
        return $this->belongsTo(Company::class);
    }

    public function projects() : HasMany {
        return $this->hasMany(ProjectWorker::class);
    }

    public function files() : HasMany {
        return $this->hasMany(File::class, 'parent_id')->where('parent_table', '=', 'workers');
    }

    public function pauses() : HasMany {
        return $this->hasMany(WorkerPauseDate::class);
    }

    public function days() : HasMany {
        return $this->hasMany(WorkerWorkDay::class);
    }

    public function damages() : HasMany {
        return $this->hasMany(ItemDamage::class, 'responsible_id');
    }

    public function payments() : HasMany {
        return $this->hasMany(WorkerPayment::class, 'worker_id');
    }
}
