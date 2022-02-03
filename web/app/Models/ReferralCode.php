<?php

namespace App\Models;

use Sumra\SDK\Traits\OwnerTrait;
use Sumra\SDK\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ReferralCode extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UuidTrait;
    use OwnerTrait;

    const CAMPAIGN = 'Referral Program';
    const MEDIUM = 'Invite Friends';

    /**
     * @var array|string[]
     */
    public static array $rules = [
        'is_default' => 'boolean',
        'note' => 'string|max:255'
    ];

    /**
     * @var string[]
     */
    protected $appends = [
        // 'resource_url'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_default' => 'boolean'
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
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get codes / links by application
     *
     * @param $query
     * @param string|null $application_id
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function scopeByApplication($query, string $application_id = null): mixed
    {
        return $query->where('application_id', $application_id ?? request()->get('application_id'));
    }

    /**
     * Get codes / links by referral code
     *
     * @param $query
     * @param string|null $referral_code
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function scopeByReferralCode($query, string $referral_code = null): mixed
    {
        return $query->where('code', $referral_code ?? request()->get('referral_code'));
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
