<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable= [
        'address_1',
        'address_2',
        'address_3',
        'email_1',
        'email_2',
        'email_3',
        'phone_1',
        'phone_2',
        'phone_3',
        'whatsapp',
        'facebook',
        'x',
        'linkedin',
        'snapchat',
        'instagram',
        'telegram',
        'privacy_policy',
        'terms_and_conditions',
    ];
}
