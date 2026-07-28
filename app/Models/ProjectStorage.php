<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectStorage extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'project_id',
        'storage_id',
        'created_by',
    ];

    public function project() : BelongsTo {
        return $this->belongsTo(Project::class);
    }

    public function storage() : BelongsTo {
        return $this->belongsTo(Storage::class);
    }
}
