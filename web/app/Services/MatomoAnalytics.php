<?php

namespace App\Services;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MatomoAnalytics
{
    /**
     * @param null $method
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __invoke($method = null)
    {
        $this->getData($method);
    }

    /**
     * @param null $method
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getData($method = null){
        // Set parameters
        $queryParameters = [
            'module' => 'API',
            'idSite' => config('matomo-analytics.site_id'),
            'token_auth' => config('matomo-analytics.auth_token'),
            'method' => $method,
            'period' => 'year',
            'date' => '2021',
            'format' => 'JSON'
        ];

        // Get data from Matomo API
        try {
            $client = new Client();
            $response = $client->request(
                'GET',
                config('matomo-analytics.matomo_url'),
                [
                    'query' => $queryParameters
                ]
            );
        } catch (ClientException $e) {
            echo Psr7\Message::toString($e->getRequest());
            echo Psr7\Message::toString($e->getResponse());
        }


        // Check received data
        if ($response->getStatusCode() !== 200) {
            Log::warning('Data is missing');

            return false;
        }

        // Handle received data
        $data = $response->getBody()->getContents();
        if (!Str::startsWith($data, '[')) {
            $data = "[{$data}]";
        }
        $data = json_decode($data, true);

        Log::info($data);

        // Return ready data
        return $data;
    }
}
