<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectExpense extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'project_id',
        'title',
        'description',
        'amount',
        'currency',
        'date',
        'created_by',
    ];

    public function project() : BelongsTo {
        return $this->belongsTo(Project::class);
    }
}
