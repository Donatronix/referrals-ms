<?php

namespace Database\Seeders;

use App\Models\ApplicationKey;
use Illuminate\Database\Seeder;

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
