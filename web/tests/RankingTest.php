<?php

    namespace Tests;

    use App\Traits\RankingsTrait;

    class RankingTest extends TestCase
    {
        use RankingsTrait;

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


    }
