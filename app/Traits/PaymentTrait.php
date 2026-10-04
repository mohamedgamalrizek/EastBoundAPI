<?php

namespace App\Traits;

trait PaymentTrait
{

    public function bKashTokenGenerator($client)
    {
        if (settings('bkash_test_mode') == 1) {
            $base_url = 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized';
        } else {
            $base_url = 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized';
        }
        $request_data = [
            'app_key'    => settings('bkash_app_id'),
            'app_secret' => settings('bkash_app_secret')
        ];
        $request_data_json = json_encode($request_data);

        $response = $client->request('POST', "$base_url/checkout/token/grant", [
            'body' => $request_data_json,
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'password' => settings('bkash_password'),
                'username' => settings('bkash_username'),
            ],
        ]);
        $decoded_data = json_decode($response->getBody()->getContents());
        return $decoded_data->id_token;
    }
}
