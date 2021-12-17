<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ReferralCode;
use App\Models\User;
use App\Services\Firebase;
use Illuminate\Database\Seeder;

class ReferralCodesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            $codes = ReferralCode::factory()->count(mt_rand(1, 1))->create([
                'user_id' => $user->id,
            ]);

//            foreach ($codes as $code){
//                $code->link = Firebase::linkGenerate($code->code, $code->application_id);
//                $code->save();
//            }

//            // Get all applications
//            $applications = Application::byOwner($user->id)->get();
//
//            foreach ($applications as $app) {
//                ReferralCode::factory()->count(3)->create([
//                    'application_id' => $app->application_id,
//                    'link' => Firebase::linkGenerate($user->referral_code, $app->application_id)
//                ]);
//            }
        }
    }
}
