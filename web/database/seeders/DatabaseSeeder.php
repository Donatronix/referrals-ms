<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Seeds for local and staging
        if (App::environment(['local', 'staging'])) {
            $this->call([
                UsersTableSeeder::class,
                ReferralCodesTableSeeder::class,
                TotalSeeder::class,
                TransactionSeeder::class,
            ]);
        }

        // Seeds for production
        if (App::environment('production')) {

        }
    }
}
