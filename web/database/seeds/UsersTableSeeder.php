<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($a = 0; $a < 10; $a++) {
            $id = 0;

            if (mt_rand(0, 1) == 1) {
                $users = User::all();

                if ($users->count() > 0) {
                    $id = $users->random()->id;
                }
            }

            factory(User::class, 5)->create([
                'referrer_id' => $id
            ]);
        }
    }
}
