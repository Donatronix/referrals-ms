<?php

namespace Database\Seeders;

use App\Models\ReferralCode;
use Illuminate\Database\Seeder;

class ReferralCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($a = 0; $a < 30; $a++) {
            $code = ReferralCode::factory()->create();
            //  $code->link = Firebase::linkGenerate($code->code, $code->application_id);
            $code->save();
        }
    }
}
