<?php

    namespace App\Models;

    use App\Traits\RankingsTrait;
    use Carbon\Carbon;
    use Carbon\CarbonImmutable;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Sumra\SDK\Traits\UuidTrait;

    class Total extends Model
    {
        use HasFactory;
        use RankingsTrait;
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
            'is_current',
            'twenty_four_hour_percentage',
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
         *  We receive data for the informer and collect it
         *
         * @param $user_id
         *
         * @return array $informer
         */
        public static function getInformer($user_id): array
        {
            $informer = [];
            $rankings = self::getRankings();

            return [
                'rank' => $rankings->first(function ($value) use ($user_id) {
                    return $value['user_id'] === $user_id;
                })['rank'],
                'reward' => self::query()->where('user_id', $user_id)->sum('reward'),
                'growth_this_month' => self::getInvitedUsersByDate($user_id, 'current_month_count'),
            ];
        }

        /**
         * @param        $user_id
         * @param string $format
         *
         * @return int|Collection|null
         */
        public static function getInvitedUsersByDate($user_id, string $format = 'data'): Collection|int|null
        {
            $en = CarbonImmutable::now()->locale('en_US');
            return match ($format) {
                'current_week_count' => User::where('referrer_id', $user_id)
                    ->whereBetween('created_at', [$en->startOfWeek(), $en->endOfWeek()])
                    ->count(),
                'current_month_count' => User::where('referrer_id', $user_id)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->count(),
                'current_year_count' => User::where('referrer_id', $user_id)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count(),
                'last_month_count' => User::where('referrer_id', $user_id)
                    ->whereMonth('created_at', Carbon::now()->subMonth())
                    ->count(),
                'current_month_data' => User::where('referrer_id', $user_id)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->get(),
                'last_month_data' => User::where('referrer_id', $user_id)
                    ->whereMonth('created_at', Carbon::now()->subMonth())
                    ->get(),
                default => null,
            };
        }

        /**
         * @return BelongsTo
         */
        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }

    }
