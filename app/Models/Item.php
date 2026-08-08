<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'storage_id',
        'category_id',
        'picture',
        'name',
        'code',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'status',
        'is_active',
        'created_by',
    ];

    public function storage() : BelongsTo {
        return $this->belongsTo(Storage::class, 'storage_id');
    }

    public function category() : BelongsTo {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function project() : BelongsTo {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function movements() : HasMany {
        return $this->hasMany(ItemMovement::class, 'item_id');
    }

    public function damages() : HasMany {
        return $this->hasMany(ItemDamage::class, 'item_id');
    }

    public function files() : HasMany {
        return $this->hasMany(File::class, 'parent_id')->where('parent_table', '=', 'items');
    }
}
