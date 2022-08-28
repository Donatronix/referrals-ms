<?php

namespace App\Models;

use App\Traits\TextToImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Sumra\SDK\Traits\UuidTrait;

class User extends Model
{
    use HasFactory;
    use UuidTrait;
    use SoftDeletes;
    use TextToImageTrait;

    const REFERRER_POINTS = 3;

    /**
     * Type / roles of users / referrals
     */
    const TYPE_CLIENT = 10;
    const TYPE_PARTNER = 20;

    /**
     * User roles list
     *
     * @var array|int[]
     */
    public static array $types = [
        'client' => self::TYPE_CLIENT,
        'partner' => self::TYPE_PARTNER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'referrer_id',
        'name',
        'username',
        'country',
        'type'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @return HasMany
     */
    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class);
    }

    /**
     * @return HasOne
     */
    public function total(): HasOne
    {
        return $this->hasOne(Total::class);
    }

    /**
     * @return string
     */
    public function getAvatarAttribute(): string
    {
        return $this->createImage(strtoupper(substr($this->name, 0, 1)));
    }

    /**
     * @return HasOne
     */
    public function getRankAttribute()
    {
        return $this->total()->select(DB::raw('
            SELECT s.*, @rank := @rank + 1 rank FROM (
                SELECT user_id, sum(reward) TotalPoints FROM t
                GROUP BY user_id
            ) s, (SELECT @rank := 0) init
            ORDER BY TotalPoints DESC')
        );
    }
}
