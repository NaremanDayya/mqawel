<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    use HasFactory;

    protected $fillable= [
        'company_id',
        'parent_table',
        'parent_id',
        'name',
        'description',
        'file',
        'issue_date',
        'expiry_date',
        'validity_type',
        'category',
        'is_active',
        'created_by',
        'master_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function company() : BelongsTo {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function parentUser() : BelongsTo {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function parentCompany() : BelongsTo {
        return $this->belongsTo(Company::class, 'parent_id');
    }

    public function parentWorker() : BelongsTo {
        return $this->belongsTo(Worker::class, 'parent_id');
    }

    public function parentStorage() : BelongsTo {
        return $this->belongsTo(Storage::class, 'parent_id');
    }

    public function parentProject() : BelongsTo {
        return $this->belongsTo(Project::class, 'parent_id');
    }

    public function parentItem() : BelongsTo {
        return $this->belongsTo(StorageItem::class, 'parent_id');
    }

    public function parentContractor() : BelongsTo {
        return $this->belongsTo(Contractor::class, 'parent_id');
    }

    public function downloadFilename(): string
    {
        $extension = pathinfo((string) $this->file, PATHINFO_EXTENSION);
        $safeName = trim(preg_replace('/[\\\\\/:*?"<>|]+/u', ' ', (string) $this->name));
        $safeName = $safeName !== '' ? $safeName : 'document';

        return $extension ? "{$safeName}.{$extension}" : $safeName;
    }
}
