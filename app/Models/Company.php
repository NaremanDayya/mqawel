<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable= [
        'picture',
        'name',
        'email',
        'phone',
        'website',
        'business_number',
        'tax_number',
        'activity',
        'description',
        'founded_year',
        'address',
        'services',
        'about_to_expire_days',
        'notification_settings',
        'dashboard_widgets',
        'document_categories',
        'is_active',
        'is_verified',
        'master_id',
    ];

    protected $casts = [
        'notification_settings' => 'array',
        'dashboard_widgets' => 'array',
        'document_categories' => 'array',
        'services' => 'array',
        'is_verified' => 'boolean',
    ];

    public function master() : BelongsTo {
        return $this->belongsTo(Master::class, 'master_id');
    }

    public function activityLogs() : HasMany {
        return $this->hasMany(CompanyActivityLog::class);
    }

    public function workers() : HasMany {
        return $this->hasMany(Worker::class, 'company_id');
    }

    public function projects() : HasMany {
        return $this->hasMany(Project::class, 'company_id');
    }

    public function users() : HasMany {
        return $this->hasMany(User::class, 'company_id');
    }

    public function subscriptions() : HasMany {
        return $this->hasMany(Subscription::class, 'company_id');
    }
}
