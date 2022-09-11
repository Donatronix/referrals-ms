<?php

    namespace Tests;

    use App\Models\Total;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\DB;

    class RankingTest extends TestCase
    {
        /**
         * A basic test example.
         *
         * @return void
         */
        public function testRankings()
        {
            dd($this->topReferralBonus());
            $this->assertTrue(true);
        }

        protected function getRankings(): Collection
        {
            $user_id = "973de1d6-bce8-441d-b5f3-1363454cdb9e";
            $users = DB::table('totals')->distinct('user_id')->get('user_id');
            $retVal = $users->map(function ($item) {
                return [
                    'user_id' => $item->user_id,
                    'reward' => Total::query()->where('user_id', $item->user_id)->sum('reward'),
                ];
            });

            return collect($retVal)->sortByDesc('reward')
                ->values()->map(function ($item, $key) {
                    return [
                        'user_id' => $item['user_id'],
                        'rank' => $key + 1,
                        'reward' => $item['reward'],
                    ];
                });
        }

        protected function topReferralBonus()
        {
            return $this->getRankings()->first()['reward'];
        }
    }
