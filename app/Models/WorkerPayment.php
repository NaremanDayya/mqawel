<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerPayment extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'worker_id',
        'amount',
        'currency',
        'title',
        'payment_type',
        'payment_date',
        'payment_method',
        'notes',
        'status',
        'created_by',
    ];

    public function worker() : BelongsTo {
        return $this->belongsTo(Worker::class, 'worker_id');
    }
}
