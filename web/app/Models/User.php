<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class User extends Model
{
    const REFERRER_POINTS = 3;
    const INSTALL_POINTS = 5;

    const STATUS_APPROVED = 1;
    const STATUS_NOT_APPROVED = 2;
    const STATUS_BLOCKED = 3;

    protected $fillable = [
        'app_user_id',
        'user_name',
        'referrer_id',
        'referral_code',
        'status'
    ];

    protected $dates = [
        'updated_at'
    ];

    /**
     * Boot the model.
     *
     * @return  void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($obj) {
            do {
                // generate a random string using Laravel's str_random helper
                $referralCode = Str::random(10);
            }
            //check if the token already exists and if it does, try again
            while (User::where('referral_code', $referralCode)->first());

            $obj->referral_code = (string) $referralCode;
        });
    }

    public function devices(){
        return $this->hasMany(Device::class);
    }
 }
