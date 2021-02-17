<?php

use Illuminate\Database\Seeder;
use App\Models\Application;

class ApplicationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        factory(Application::class, 5)->create([
            'referrer_id' => $id
        ]);
    }
}
