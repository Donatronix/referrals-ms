<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    const INSTALLED_OK = 1;
    const INSTALLED_APPROVE = 2;
    const INSTALLED_REJECTED = 3;

    const REFERRER_OK = 1;
    const REFERRER_APPROVE = 2;
    const REFERRER_REJECTED = 3;

    protected $casts = [
        'metadata' => 'json'
    ];

    protected $fillable = [
        'referrer_code',
        'package_name',
        'device_id',
        'device_name',
        'ip',
        'metadata'
    ];
}
