<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run(): void
    {
        // Generate default users
        foreach(config('settings.default_users_ids') as $uuid){
            User::factory()->create([
                'id' => $uuid
            ]);
        }

        // Adding additional users
        $users = User::all();
        foreach ($users as $user){
            User::factory()->count(100)->create([
                'referrer_id' => $user->id
            ]);
        }
    }
}
