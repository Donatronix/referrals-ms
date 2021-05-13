<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralCodes extends Model
{
    const CAMPAIGN = 'Referral Program';
    const MEDIUM = 'Invite Friends';

    const ANDROID_PACKAGE_NAME = 'net.sumra.android';
    //const ANDROID_MIN_PACKAGE_VERSION = '20040902';

    protected $appends = ['resource_url'];

    protected $fillable = [
        'user_id',
        'package_name',
        'referral_link'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute()
    {
        return url('/admin/links/'.$this->getKey());
    }
}
