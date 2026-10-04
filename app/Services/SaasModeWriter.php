<?php

namespace App\Services;

use RuntimeException;

/**
 * Flips `enabled` in config/saas.php.
 *
 * SaaS mode is a code-level choice, not per-deployment configuration, so it
 * lives in the config file rather than .env — a config value also survives
 * `php artisan config:cache`, which an env() call read at runtime does not.
 * Both the installer and `php artisan saas:mode` change it through here so
 * there is one way to write it.
 */
class SaasModeWriter
{
    public function set(bool $enabled): void
    {
        $path = config_path('saas.php');

        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('config/saas.php is missing or unreadable.');
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Unable to read config/saas.php.');
        }

        $value   = $enabled ? 'true' : 'false';
        $pattern = "/^(\s*'enabled'\s*=>\s*)(?:true|false)(\s*,)$/m";

        if (! preg_match($pattern, $contents)) {
            throw new RuntimeException("Could not find the 'enabled' line in config/saas.php.");
        }

        $updated = preg_replace($pattern, "\${1}{$value}\${2}", $contents, 1);

        // Write to a sibling file first so a failed write cannot leave the
        // config truncated — a half-written config file takes the site down.
        $temporary = $path . '.tmp-' . bin2hex(random_bytes(8));
        if (file_put_contents($temporary, $updated, LOCK_EX) === false || ! @rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('Unable to write config/saas.php. Check the file permissions.');
        }

        config(['saas.enabled' => $enabled]);
    }
}
