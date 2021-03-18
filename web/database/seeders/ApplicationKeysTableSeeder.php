<?php

use Illuminate\Database\Seeder;
use App\Models\ApplicationKey;

class ApplicationKeysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        factory(ApplicationKey::class, 5)->create();
    }
}
