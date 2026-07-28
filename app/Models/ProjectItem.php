<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectItem extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'project_id',
        'storage_item_id',
        'quantity',
        'date',
        'created_by',
    ];

    public function project() : BelongsTo {
        return $this->belongsTo(Project::class);
    }

    public function item() : BelongsTo {
        return $this->belongsTo(Item::class);
    }
}
