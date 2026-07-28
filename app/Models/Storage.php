<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Storage extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'name',
        'address',
        'is_active',
        'created_by',
    ];

    public function items() : HasMany {
        return $this->hasMany(Item::class);
    }
}
