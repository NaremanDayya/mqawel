<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectWorker extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'project_id',
        'worker_id',
        'date',
        'created_by',
    ];

    public function project() : BelongsTo {
        return $this->belongsTo(Project::class);
    }

    public function worker() : BelongsTo {
        return $this->belongsTo(Worker::class);
    }
}
