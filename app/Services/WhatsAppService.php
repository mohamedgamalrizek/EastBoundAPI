<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public function send(string $phone, string $message): bool
    {
        if (! decrypt_setting('whatsapp_access_token') || ! settings('whatsapp_phone_number_id')) return false;
        Http::timeout(15)->withToken(decrypt_setting('whatsapp_access_token'))->post('https://graph.facebook.com/'.(settings('whatsapp_api_version') ?: 'v21.0').'/'.settings('whatsapp_phone_number_id').'/messages', [
            'messaging_product' => 'whatsapp', 'to' => preg_replace('/\D/', '', $phone), 'type' => 'text', 'text' => ['body' => $message],
        ])->throw();
        return true;
    }
}
