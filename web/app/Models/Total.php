<?php

namespace App\Models;

use App\Traits\UuidTrait;
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
        'username' => 'required|string|max:255',
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
        'username',
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

    public static function getInformer ($user_id)
    {
        $informer = [];
        $users = self::orderBy('amount', 'DESC')->get();
        $rank = 1;
        foreach ($users as $user)
        {
            if($user->user_id == $user_id)
            {
                $informer = [
                    'rank' => $rank,
                    'reward' => $user->reward,
                    'grow_this_month' => User::getInvitedUsersByDate($user_id, 'current_month_count')
                ];
                break;
            }
            $rank++;
        }

        return $informer;
    }
}
