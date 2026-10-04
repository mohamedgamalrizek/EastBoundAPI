<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MiM SMS (mimsms.com) gateway — Bangladesh.
 *
 * Credentials live in Settings → SMS (`mim_sms_*`), so the buyer enters their
 * own keys in the panel; nothing is read from .env. Every call is logged to the
 * `sms` channel so a failed OTP can be traced without turning on debug.
 */
class MimSmsService
{
    protected $userName, $apiKey;
    public bool $isServiceActive = false;

    private const BASE_URL           = 'https://api.mimsms.com';
    private const SEND_ENDPOINT      = 'api/SmsSending/SMS';          // post
    private const SEND_MANY_ENDPOINT = 'api/SmsSending/OneToMany';    // post
    private const BALANCE_ENDPOINT   = 'api/SmsSending/balanceCheck'; // post

    public function __construct()
    {
        $this->apiKey          = settings('mim_sms_api_key');
        $this->userName        = settings('mim_sms_username');
        $this->isServiceActive = (bool) settings('mim_sms_status');
    }

    private function callApi(string $endpoint, array $params = [])
    {
        $url = null;

        try {
            if (! $this->isServiceActive) {
                throw new \Exception('mimSMS gateway disabled');
            }

            $url = rtrim(self::BASE_URL, '/') . '/' . ltrim($endpoint, '/');

            $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];

            $requiredParams = ['apiKey' => $this->apiKey, 'userName' => $this->userName];

            $postData = array_merge($params, $requiredParams);

            $response = Http::withHeaders($headers)->post($url, $postData);

            $data = $response->json();

            if (($data['statusCode'] ?? null) != 200) {
                Log::channel('sms')->error('Api response', $data ?? ['raw' => $response->body()]);
                throw new \Exception($data['responseResult'] ?? $data['title'] ?? 'Unknown api error');
            }

            Log::channel('sms')->info('Api response', $data);

            return $data;
        } catch (\Throwable $th) {
            Log::channel('sms')->error($th->getMessage(), ['params' => $params, 'url' => $url]);
            throw new \Exception($th->getMessage());
        }
    }

    public function sendSMS(string|array $phoneNumbers, string $message): array
    {
        try {
            $endpoint = is_array($phoneNumbers) ? self::SEND_MANY_ENDPOINT : self::SEND_ENDPOINT;

            $mobileNumber = is_array($phoneNumbers) ? implode(',', $phoneNumbers) : $phoneNumbers;

            $mobileNumber = str_replace('+', '', $mobileNumber); // the API rejects a leading +

            $params = [
                'MobileNumber'    => $mobileNumber,
                'Message'         => $message,
                'SenderName'      => settings('mim_sms_sender_id'),
                'TransactionType' => 'T',
            ];

            $data = $this->callApi($endpoint, $params);

            return ['success' => true, 'message' => $data['responseResult'] ?? null, 'data' => $data];
        } catch (\Throwable $th) {
            return ['success' => false, 'message' => $th->getMessage()];
        }
    }

    public function getBalance(): float
    {
        try {
            $data = $this->callApi(self::BALANCE_ENDPOINT, []);

            return (float) ($data['responseResult'] ?? 0);
        } catch (\Throwable $th) {
            return 0;
        }
    }
}
