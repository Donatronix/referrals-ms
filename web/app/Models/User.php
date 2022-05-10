<?php

namespace App\Models;

use App\Traits\TextToImageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\UuidTrait;

class User extends Model
{
    use HasFactory;
    use UuidTrait;
    use SoftDeletes;
    use TextToImageTrait;

    const REFERRER_POINTS = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'referrer_id',
        'application_id',
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

    protected $appends = ['avatar'];

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

    public function getAvatarAttribute(): string
    {
        return $this->createImage(strtoupper(substr($this->name, 0, 1)))->showImage();
    }


}
