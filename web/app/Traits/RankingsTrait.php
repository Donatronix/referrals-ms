<?php

    namespace App\Traits;

    use App\Models\Total;
    use Illuminate\Support\Collection;

    trait RankingsTrait
    {


        /**
         * @return mixed
         */
        protected function topReferralBonus(): mixed
        {
            return $this->rankings->first()['reward'];
        }

        /**
         * @return Collection
         */
        protected static function getRankings(): Collection
        {
            $users = Total::distinct('user_id')->get('user_id');
            $retVal = $users->map(function ($item) {
                return [
                    'user_id' => $item->user_id,
                    'reward' => Total::query()->where('user_id', $item->user_id)->sum('reward'),
                ];
            });

            return collect($retVal)
                ->sortByDesc('reward')
                ->values()
                ->map(function ($item, $key) {
                    return [
                        'user_id' => $item['user_id'],
                        'rank' => $key + 1,
                        'reward' => $item['reward'],
                    ];
                });
        }
    }
