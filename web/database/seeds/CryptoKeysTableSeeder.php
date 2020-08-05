<?php

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
        factory(\App\Models\ApplicationKey::class, 5)->create();
    }
}
