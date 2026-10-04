<?php

namespace App\Services\Mail;

/**
 * Pushes Settings -> Mail into Laravel's mail config at runtime.
 *
 * Mail is configured from the admin panel, not `.env`, so the config has to be
 * rewritten before anything is sent. This used to be a copy-pasted block in
 * SettingsRepository and UserRepository (four copies); a driver added to one
 * copy and not the others produced mail that worked from the test button and
 * failed everywhere else. One place now.
 *
 * Call apply() immediately before Mail::send/to.
 */
class MailConfig
{
    /** Drivers the admin panel offers. */
    public const DRIVERS = ['smtp', 'sendmail', 'gmail_api'];

    public static function driver(): string
    {
        $driver = (string) settings('mail_driver');

        return in_array($driver, self::DRIVERS, true) ? $driver : 'smtp';
    }

    public static function apply(): void
    {
        $driver = self::driver();

        \config([
            'mail.default'      => $driver,
            'mail.from.address' => settings('mail_address'),
            'mail.from.name'    => settings('mail_name'),
        ]);

        match ($driver) {
            'sendmail'  => self::applySendmail(),
            'gmail_api' => self::applyGmailApi(),
            default     => self::applySmtp(),
        };
    }

    private static function applySmtp(): void
    {
        \config([
            'mail.mailers.smtp.host'       => settings('mail_host'),
            'mail.mailers.smtp.port'       => settings('mail_port'),
            'mail.mailers.smtp.encryption' => settings('mail_encryption'),
            'mail.mailers.smtp.username'   => settings('mail_username'),
            'mail.mailers.smtp.password'   => mail_password(),
        ]);
    }

    private static function applySendmail(): void
    {
        \config([
            'mail.mailers.sendmail.path' => settings('sendmail_path'),
        ]);
    }

    /**
     * Nothing to configure on the mailer itself — the transport reads its
     * OAuth values from settings at send time. Registering the entry keeps
     * `mail.default => gmail_api` from resolving to a missing mailer.
     */
    private static function applyGmailApi(): void
    {
        \config([
            'mail.mailers.gmail_api' => ['transport' => 'gmail_api'],
        ]);
    }
}
