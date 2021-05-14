<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReferralCode;


class ReferralCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ReferralCode::factory()->count(3)->create();
    }
}
