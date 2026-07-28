<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contractor extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'name',
        'type',
        'phone',
        'email',
        'fax',
        'website',
        'address',
        'registry_number',
        'tax_number',
        'category',
        'fields',
        'years_of_experience',
        'number_of_projects',
        'areas',
        'is_active',
        'created_by',
    ];

    public function company() : BelongsTo {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function files() : HasMany {
        return $this->hasMany(File::class, 'parent_id')->where('parent_table', '=', 'contractors');
    }

    public function createdBy() : BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }
}
