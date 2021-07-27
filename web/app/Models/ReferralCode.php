<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReferralCode extends BaseModel
{
    use HasFactory;
    use UuidTrait;

    const CAMPAIGN = 'Referral Program';
    const MEDIUM = 'Invite Friends';

    const ANDROID_PACKAGE_NAME = 'net.sumra.android';
    //const ANDROID_MIN_PACKAGE_VERSION = '20040902';

    /**
     * @var string[]
     */
    protected $appends = [
        'resource_url'
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'code',
        'application_id',
        'user_id',
        'link',
        'is_default',
        'note'
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public static array $rules = [
        'is_default' => 'integer|max:1',
        'note' => 'string|max:255'
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

            $obj->setAttribute('code', (string)$referralCode);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get codes / links by application
     *
     * @param $query
     * @param $application_id
     *
     * @return mixed
     */
    public function scopeByApplication($query, $application_id = null)
    {
        return $query->where('application_id', $application_id);
    }

    /* ************************ ACCESSOR ************************* */
    /**
     * @return string
     */
    public function getResourceUrlAttribute(): string
    {
        return url('/admin/links/' . $this->getKey());
    }
}
