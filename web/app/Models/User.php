<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class User extends Model
{
    const REFERRER_POINTS = 3;
    const INSTALL_POINTS = 5;

    const STATUS_APPROVED = 1;
    const STATUS_NOT_APPROVED = 2;
    const STATUS_BLOCKED = 3;

    protected $appends = [
        'resource_url'
    ];

    protected $fillable = [
        'user_id',
        'user_name',
        'referral_code',
        'referrer_id',
        'status'
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

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

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
            } //check if the token already exists and if it does, try again
            while (self::where('referral_code', $referralCode)->first());

            $obj->referral_code = (string)$referralCode;
        });
    }

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute(): string
    {
        return url('/admin/users/' . $this->getKey());

        //return redirect()->route("dashboard.referral-users.bulk-destroy");
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
