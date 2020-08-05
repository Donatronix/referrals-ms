<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    const INSTALLED_NO = 0;
    const INSTALLED_OK = 1;
    const INSTALLED_APPROVE = 2;
    const INSTALLED_REJECTED = 3;

    const REFERRER_NO = 0;
    const REFERRER_OK = 1;
    const REFERRER_APPROVE = 2;
    const REFERRER_REJECTED = 3;

    protected $appends = [
        'resource_url'
    ];

    protected $casts = [
        'metadata' => 'json'
    ];

    protected $fillable = [
        'package_name',
        'device_id',
        'device_name',
        'ip',
        'metadata',
        'referrer_code',
        'user_id',
        'user_status',
        'referrer_id',
        'referrer_status'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute()
    {
        return url('/admin/applications/'.$this->getKey());
    }
}
