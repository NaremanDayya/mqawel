<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'icon',
        'tone',
        'message',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function log(int $companyId, string $message, string $icon = 'heroicon-o-pencil-square', string $tone = 'gray'): self
    {
        return static::create([
            'company_id' => $companyId,
            'icon' => $icon,
            'tone' => $tone,
            'message' => $message,
            'created_at' => now(),
        ]);
    }
}
