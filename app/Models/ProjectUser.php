<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectUser extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'project_id',
        'user_id',
        'date',
        'created_by',
    ];

    public function project() : BelongsTo {
        return $this->belongsTo(Project::class);
    }

    public function user() : BelongsTo {
        return $this->belongsTo(User::class);
    }
}
