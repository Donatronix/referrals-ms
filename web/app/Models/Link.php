<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    const CAMPAIGN = 'Referral Program';
    const MEDIUM = 'Invite Friends';

    const ANDROID_PACKAGE_NAME = 'net.sumra.android';
    //const DEFAULT_ANDROID_MIN_PACKAGE_VERSION = '20040902';

    protected $fillable = [
        'app_user_id',
        'package_name',
        'referral_link'
    ];
 }
