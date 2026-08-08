<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerWorkDay extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'worker_id',
        'day',
        'date',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function worker() : BelongsTo {
        return $this->belongsTo(Worker::class);
    }
}
