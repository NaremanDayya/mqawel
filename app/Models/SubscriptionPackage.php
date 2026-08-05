<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPackage extends Model
{
    use HasFactory;

    protected $fillable= [
        'picture',
        'title',
        'description',
        'period',
        'price',
        'currency',
        'has_workers',
        'has_projects',
        'has_storages',
        'has_items',
        'has_item_categories',
        'has_item_movements',
        'has_workers_report',
        'has_worker_expenses_report',
        'has_expired_files_report',
        'has_project_expenses_report',
        'max_projects',
        'max_workers',
        'traditional_cost_items',
        'savings_note',
        'feature_bullets',
        'cta_label',
        'is_active',
        'master_id',
    ];

    protected $casts = [
        'traditional_cost_items' => 'array',
        'feature_bullets' => 'array',
    ];

    public function subscriptions() : HasMany {
        return $this->hasMany(Subscription::class, 'package_id');
    }
}
