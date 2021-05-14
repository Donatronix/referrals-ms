<?php
namespace Database\Seeders;

use App\Models\Link;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Services\Firebase;
use App\Models\Application;

class LinksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach($users as $user){
            // Get all applications
            $applications = Application::where('user_id', $user->id)->get();

            foreach($applications as $app){
                Link::factory()->count(3)->create([
                    'package_name' => $app->package_name,
                    'user_id' => $user->id,
                    'referral_link' => Firebase::linkGenerate($user->referral_code, $app->package_name)
                ]);
            }

        }
    }
}
