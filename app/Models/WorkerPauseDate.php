<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerPauseDate extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'worker_id',
        'date_of_pause',
        'reason',
        'created_by',
    ];

    public function worker() : BelongsTo {
        return $this->belongsTo(Worker::class);
    }
}
