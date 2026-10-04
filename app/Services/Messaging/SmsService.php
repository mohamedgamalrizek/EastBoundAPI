<?php

namespace App\Services\Messaging;

use App\Enums\SmsGatewayProvider;

/**
 * Entry point for every outgoing SMS. Picks whichever gateway the agency has
 * switched on in Settings → SMS. Never throws: a dead gateway must not take a
 * signup or a password reset down with it — the caller reads `success`.
 */
class SmsService
{
    public static function send(string|array $phoneNumbers, string $message): array
    {
        try {
            return match (true) {
                (bool) settings('mim_sms_status') => app(MimSmsService::class)->sendSMS($phoneNumbers, $message),
                default                           => throw new \Exception('No SMS gateway enabled'),
            };
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function getBalance(SmsGatewayProvider $provider): float
    {
        try {
            return match ($provider) {
                SmsGatewayProvider::MimSMS => (float) app(MimSmsService::class)->getBalance(),
            };
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /** True when at least one gateway is configured and switched on. */
    public static function enabled(): bool
    {
        return (bool) settings('mim_sms_status');
    }
}
