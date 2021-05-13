<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'id',
        'tier',    // enum of ['basic', 'bronze', 'silver', 'gold']
        'regstate', // enum of ['new','registered','kyc']
        'isbustedtime', // true if reach Tier::BURSTED_MIN_HOURS
        'isbustedmoney', // true if reach Tier::BURSTED_MIN_MONEY
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
}
