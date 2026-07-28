<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemCategory extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'name',
        'is_active',
        'created_by',
    ];

    public function items() : HasMany {
        return $this->hasMany(Item::class, 'category_id');
    }
}
