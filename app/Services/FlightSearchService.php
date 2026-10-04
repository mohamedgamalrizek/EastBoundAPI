<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FlightSearchService
{
    private function token(): string
    {
        return Cache::remember('amadeus.access_token', 1500, function () {
            $response = Http::asForm()->post(rtrim(settings('amadeus_api_url') ?: 'https://test.api.amadeus.com', '/').'/v1/security/oauth2/token', [
                'grant_type' => 'client_credentials', 'client_id' => decrypt_setting('amadeus_api_key'), 'client_secret' => decrypt_setting('amadeus_api_secret'),
            ])->throw();
            return $response->json('access_token');
        });
    }

    public function search(array $input): array
    {
        if (! decrypt_setting('amadeus_api_key') || ! decrypt_setting('amadeus_api_secret')) throw new RuntimeException('Live flights are not configured. Add Amadeus credentials.');
        $query = ['originLocationCode' => strtoupper($input['origin']), 'destinationLocationCode' => strtoupper($input['destination']), 'departureDate' => $input['departure_date'], 'adults' => $input['adults'], 'currencyCode' => $input['currency'], 'max' => 20];
        if (! empty($input['return_date'])) $query['returnDate'] = $input['return_date'];
        return Http::withToken($this->token())->get(rtrim(settings('amadeus_api_url') ?: 'https://test.api.amadeus.com', '/').'/v2/shopping/flight-offers', $query)->throw()->json('data', []);
    }
}
