<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable= [
        'ip_address',
        'name',
        'phone',
        'email',
        'company_name',
        'purpose',
        'title',
        'description',
        'is_open',
    ];
}
