<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stats extends Model
{
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
