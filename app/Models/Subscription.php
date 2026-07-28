<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'package_id',
        'period',
        'starting_date',
        'ending_date',
        'price',
        'currency',
        'payment_method',
        'payment_transaction_id',
        'payment_date',
        'receipt',
        'notes',
        'is_active',
        'master_id',
    ];

    public function company() : BelongsTo {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function package() : BelongsTo {
        return $this->belongsTo(SubscriptionPackage::class, 'package_id');
    }
}
