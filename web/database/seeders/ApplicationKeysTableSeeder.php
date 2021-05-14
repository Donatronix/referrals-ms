<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApplicationKey;

class ApplicationKeysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ApplicationKey::factory()->count(5)->create();
    }
}
