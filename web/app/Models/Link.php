<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    const CAMPAIGN = 'Referral Program';
    const MEDIUM = 'Invite Friends';

    const ANDROID_PACKAGE_NAME = 'net.sumra.android';
    //const ANDROID_MIN_PACKAGE_VERSION = '20040902';

    protected $fillable = [
        'user_id',
        'package_name',
        'referral_link'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $appends = ['resource_url'];

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute()
    {
        return url('/admin/links/'.$this->getKey());
    }
}
