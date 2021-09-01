<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TotalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Total::factory()->count(20)->create();
    }
}
