<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            ApplicationKeysTableSeeder::class,
            UsersTableSeeder::class,
            ApplicationsTableSeeder::class,
            LinksTableSeeder::class,
            ReferralCodeSeeder::class
        ]);
    }
}
