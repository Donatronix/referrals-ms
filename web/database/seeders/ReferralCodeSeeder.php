<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReferalCode;


class ReferralCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ReferalCode::factory()->count(3)->create();
    }
}
