<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory;
    use UuidTrait;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'referrer_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class);
    }

    public static function getInvitedUsersByDate($user_id, $format = 'data')
    {
        switch ($format) {
            case 'current_month_count':
                return User::where('referrer_id', $user_id)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->count();

            case 'last_month_count':
                return User::where('referrer_id', $user_id)
                    ->whereMonth('created_at', Carbon::now()->subMonth())
                    ->count();

            case 'current_month_data':
                return User::where('referrer_id', $user_id)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->get();

            case 'last_month_data':
                return User::where('referrer_id', $user_id)
                    ->whereMonth('created_at', Carbon::now()->subMonth())
                    ->get();
        }
    }
}
