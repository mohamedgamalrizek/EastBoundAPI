<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * Laravel mail transport that hands the finished message to the Gmail API
 * instead of opening an SMTP connection.
 *
 * The point is port 443. Shared hosts routinely block outbound 587 and 465, so
 * SMTP fails with a connection timeout the buyer has no way to fix from their
 * own control panel — the host has to lift it. The Gmail API travels over
 * ordinary HTTPS, which is never blocked, so mail keeps working on hosting
 * where SMTP simply cannot.
 *
 * Symfony has already built the RFC 2822 message by the time doSend() runs, so
 * there is nothing to assemble here: attachments, HTML parts, headers, the
 * signature — all of it is in toString(). Gmail wants that base64url encoded.
 */
class GmailApiTransport extends AbstractTransport
{
    private const SEND_URL = 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send';

    protected function doSend(SentMessage $message): void
    {
        $bearer = GmailApiToken::accessToken();

        if (! $bearer) {
            throw new \RuntimeException(
                'Gmail API is not configured, or its refresh token is no longer valid. '
                . 'Check Settings -> Mail: Client ID, Client Secret and Refresh Token.'
            );
        }

        $response = Http::withToken($bearer)
            ->timeout(30)
            ->post(self::SEND_URL, [
                // base64url — Gmail rejects standard base64: the +/ characters
                // and the = padding are not valid in this field.
                'raw' => rtrim(strtr(base64_encode($message->toString()), '+/', '-_'), '='),
            ]);

        if ($response->successful()) {
            return;
        }

        // A rotated secret or a revoked grant shows up here. Drop the cached
        // bearer so the next attempt mints a fresh one instead of replaying a
        // token that Google has already stopped honouring.
        if ($response->status() === 401) {
            GmailApiToken::forget();
        }

        throw new \RuntimeException(sprintf(
            'Gmail API refused the message (HTTP %d): %s',
            $response->status(),
            mb_substr((string) $response->json('error.message', $response->body()), 0, 300)
        ));
    }

    public function __toString(): string
    {
        return 'gmail+api://';
    }
}
