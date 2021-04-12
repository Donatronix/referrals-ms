<?php

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
        for ($a = 0; $a < 10; $a++) {
            $id = 0;

            if (random_int(0, 1) === 1) {
                $users = User::all();

                if ($users->count() > 0) {
                    $id = $users->random()->id;
                }
            }

            User::factory()->count(5)->create([
                'referrer_id' => $id
            ]);
        }
    }
}
