<?php

use Illuminate\Database\Seeder;

class CryptoKeysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\CryptoKey::class, 5)->create();
    }
}
