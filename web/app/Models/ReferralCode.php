<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\UuidTrait;

class ReferralCode extends Model
{
    use HasFactory;
    use UuidTrait;


    const CAMPAIGN = 'Referral Program';
    const MEDIUM = 'Invite Friends';

    //const ANDROID_MIN_PACKAGE_VERSION = '20040902';

    protected $appends = ['resource_url'];

    protected $fillable = [
        'user_id',
        'referral_link',
        'code',
        'is_default',
        'application_id'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
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
                $referralCode = Str::random(8);
            } //check if the token already exists and if it does, try again
            while (self::where('code', $referralCode)->first());

            $obj->code = (string)$referralCode;
        });
    }

    public function referralcodesUser()
    {
        return $this->belongsTo(User::class);
    }

    public static function getUserByReferralCode($referral_code)
    {
        return $referral_code ? self::where('code', $referral_code)->first() : false;
    }

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute()
    {
        return url('/admin/links/'.$this->getKey());
    }
}
