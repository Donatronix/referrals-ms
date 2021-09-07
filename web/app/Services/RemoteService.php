<?php


namespace App\Services;


use App\Models\Total;

class RemoteService
{
    /**
     *  We receive data from a remote microservice and write it to the leaderboard
     *
     * @param $data
     * @return bool
     */
    public static function accrualRemuneration ($data)
    {
        if($data !== null)
        {
            // if the user data came from the membership microservice, we try to find the user in the leaderboard
            $data_total = Total::where('user_id', $data['id'])
                ->first();

            // if there is something in the leaderboard, update, if not, create a record
            if($data_total == null)
            {
                $data_total = Total::create([
                    'user_id' => $data['id'],
                    'amount' => 1,
                    'reward' => $data['reward'],
                ]);
            }
            else{
                $data_total->update([
                    'amount' => $data_total->amount + 1,
                    'reward' => $data_total->reward + $data['reward'],
                ]);
            }

            // in any case, we will enter the data about the incoming data in the transaction history
            $transaction = \App\Models\Transaction::create([
                'user_id' => $data['id'],
                'user_plan' => $data['plan'],
                'reward' => $data['reward'],
                'currency' => '$',
//                'currency' => $data['level']['currency'],
                'operation_name' => 'invitation reward',
            ]);

            return true;
        }
        return false;
    }

    public static function sendData ($action, $data, $microservice)
    {
        \PubSub::publish($action, $data, $microservice);
    }
}
