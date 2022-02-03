<?php

namespace App\Models;

use Sumra\SDK\Traits\UuidTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Total extends Model
{
    use HasFactory;
    use UuidTrait;
    use SoftDeletes;

    /**
     * @var array|string[]
     */
    public static array $rules = [
        'amount' => 'integer',
        'reward' => 'regex:/^\d*(\.\d{2})?$/',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'amount',
        'reward',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     *  We receive data for the informer and collect it
     *
     * @param $user_id
     *
     * @return array $informer
     */
    public static function getInformer($user_id)
    {
        $informer = [];
        $users = self::orderBy('amount', 'DESC')
            ->orderBy('reward', 'DESC')
            ->get();
        $rank = 1;
        foreach ($users as $user) {
            if ($user->user_id == $user_id) {
                $informer = [
                    'rank' => $rank,
                    'reward' => $user->reward,
                    'grow_this_month' => Total::getInvitedUsersByDate($user_id, 'current_month_count')
                ];
                break;
            }
            $rank++;
        }

        return $informer;
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
