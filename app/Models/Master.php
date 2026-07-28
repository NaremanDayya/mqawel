<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;

class Master extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable= [
        'ip_address',
        'role',
        'picture',
        'name',
        'email',
        'phone',
        'email_verified_at',
        'password',
        'is_active',
        'master_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getFilamentAvatarUrl(): ?string
    {
        return !empty($this->picture) ? asset('storage/'.$this->picture) : asset('images/no_profile_picture.png');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if(Auth::user() && !Auth::user()->is_active){
            return false;
        }

        return true;
    }
}
