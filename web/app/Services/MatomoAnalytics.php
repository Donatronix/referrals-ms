<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MatomoAnalytics
{
    /**
     * @return false
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __invoke()
    {
        $queryParameters = [
            'module' => 'API',
            'idSite' => config('matomo-analytics.site_id'),
            'token_auth' => config('matomo-analytics.auth_token'),
//            'method' => 'UserId.getUsers',
            'method' => 'Live.getMostRecentVisitorId',
            'period' => 'day',
            'date' => '2021-02-16',
            'format' => 'JSON'
        ];

        $client = new Client();

        $response = $client->request(
            'GET',
            config('matomo-analytics.matomo_url'),
            [
                'query' => $queryParameters
            ]
        );

        if($response->getStatusCode() === 200){

            $data = $response->getBody()->getContents();

         //   dd($data);

            $data = json_decode("[{$data}]");

            Log::info($data);
        }

        return false;
    }
}
