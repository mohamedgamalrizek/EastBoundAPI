<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The one verification email: account verification and password reset, web and
 * app alike, all send this. Subject and body come from the language files so it
 * follows the site's language like every other message.
 */
class OtpCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $code,
        public int $minutes,
    ) {}

    public function build()
    {
        // The settings key is `name` — `app_name` has never existed, so this
        // silently fell back to APP_NAME and ignored the agency's own name.
        $appName = settings('name') ?: config('app.name');

        return $this->subject(str_replace(':app', $appName, ___('mail.otp_subject')))
            ->view('emails.otp_code', [
                'name'    => $this->name,
                'code'    => $this->code,
                'minutes' => $this->minutes,
                'appName' => $appName,
            ]);
    }
}
