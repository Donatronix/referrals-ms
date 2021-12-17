<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            ReferralCodesTableSeeder::class,
            TotalSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
