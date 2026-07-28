<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemDamage extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'storage_id',
        'item_id',
        'responsible_id',
        'quantity',
        'damage_date',
        'notes',
        'created_by',
    ];

    public function company() : BelongsTo {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function storage() : BelongsTo {
        return $this->belongsTo(Storage::class, 'storage_id');
    }

    public function item() : BelongsTo {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function responsible() : BelongsTo {
        return $this->belongsTo(Worker::class, 'responsible_id');
    }

    public function createdBy() : BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }
}
