<?php

namespace App\Traits;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait LeaderboardTrait
{
    /**
     * @param Request $request
     * @return LengthAwarePaginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function leaderboard(Request $request): LengthAwarePaginator
    {
        $filter = strtolower($request->filter);

        $channels = $this->getFilterQuery(DB::table('referral_codes')
            ->distinct('application_id')
            ->select('application_id', 'user_id'), $filter);

        $totalReward = $this->getFilterQuery(DB::table('totals')
            ->select('user_id', 'twenty_four_hour_percentage', DB::raw('SUM(reward) as endOfYearCashPrize')), $filter)
            ->groupBy('user_id', 'twenty_four_hour_percentage');


        return DB::table('users')
            ->whereNotNull('referrer_id')->distinct('referrer_id')
            ->select(
                'name',
                'country',
                DB::raw('COUNT(referrer_id) as referrals'),
                'totals.endOfYearCashPrize as endOfYearCashPrize',
                'totals.twenty_four_hour_percentage as twentyFourHourPercentage',
                'channels.application_id as channels'
            )
            ->joinSub($channels, 'channels', function ($join) {
                $join->on('users.referrer_id', '=', 'channels.user_id');
            })
            ->joinSub($totalReward, 'totals', function ($join) {
                $join->on('users.referrer_id', '=', 'totals.user_id');
            })
            ->groupBy('users.name', 'users.country', 'totals.endOfYearCashPrize', 'totals.twenty_four_hour_percentage', 'channels.application_id')
            ->orderBy('endOfYearCashPrize', 'desc')
            ->paginate(request()->get('limit', config('settings.pagination_limit')));
    }

    /**
     * @param $query
     * @param $filter
     *
     * @return mixed
     */
    protected function getFilterQuery($query, $filter): mixed
    {
        $en = CarbonImmutable::now()->locale('en_US');
        return match ($filter) {
            'today' => $query->whereDate('created_at', Carbon::now()->toDateString()),
            'this week' => $query->whereBetween('created_at', [$en->startOfWeek(), $en->endOfWeek()]),
            'this month' => $query->whereBetween('created_at', [$en->startOfMonth(), $en->endOfMonth()]),
            'country' => $query->where('country', request()->country ?? null),
//            'country_and_city' => $query->whereIn('user_id', $this->getUserIDByCountryCity(request()->country, request()->city)),
            default => $query->whereBetween('created_at', [$en->startOfYear(), $en->endOfYear()]),
        };
    }
}
