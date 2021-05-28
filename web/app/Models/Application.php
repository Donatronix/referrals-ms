<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    /**
     *
     */
    const INSTALLED_NO = 0;
    const INSTALLED_OK = 1;
    const INSTALLED_APPROVE = 2;
    const INSTALLED_REJECTED = 3;

    /**
     *
     */
    const REFERRER_NO = 0;
    const REFERRER_OK = 1;
    const REFERRER_APPROVE = 2;
    const REFERRER_REJECTED = 3;

    /**
     * @var string[]
     */
    protected $appends = [
        'resource_url'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'metadata' => 'json'
    ];

    /**
     * @var string[]
     */
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

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute()
    {
        return url('/admin/applications/'.$this->getKey());
    }
}
