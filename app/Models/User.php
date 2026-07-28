<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'role_id',
        'picture',
        'name',
        'email',
        'phone',
        'job_title',
        'job_description',
        'password',
        'is_admin',
        'is_active',
        'created_by',
        'master_id',
        'dashboard_preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'dashboard_preferences' => 'array',
    ];

    public function company() : BelongsTo {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function role() : BelongsTo {
        return $this->belongsTo(CompanyRole::class, 'role_id');
    }

    public function projects() : HasMany {
        return $this->hasMany(ProjectUser::class);
    }

    public function files() : HasMany {
        return $this->hasMany(File::class, 'parent_id')->where('parent_table', '=', 'users');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return !empty($this->picture) ? asset('storage/'.$this->picture) : asset('images/no_profile_picture.png');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        /*if(Auth::user() && !Auth::user()->is_active){
            return false;
        }

        if(!Auth::user()->company || !Auth::user()->company->is_active){
            return false;
        }

        $Subscription= Subscription::where(['company_id' => Auth::user()->company_id, 'is_active' => 1])->orderBy('id', 'desc')->first();

        if(!$Subscription){
            return false;
        }

        if(empty($Subscription->ending_date) || $Subscription->ending_date < date('Y-m-d')){
            return false;
        }*/

        return true;
    }
}
