<?php

namespace Modules\Installer\Services;

use RuntimeException;

class EnvironmentWriter
{
    public function write(array $values): void
    {
        $path = base_path('.env');
        $contents = is_file($path) ? file_get_contents($path) : '';
        if ($contents === false) {
            throw new RuntimeException('Unable to read the environment file.');
        }

        foreach ($values as $key => $value) {
            if (! preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) {
                throw new RuntimeException('Invalid environment key.');
            }
            $encoded = $this->encode($value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
            $contents = preg_match($pattern, $contents) ? preg_replace($pattern, "{$key}={$encoded}", $contents) : rtrim($contents)."\n{$key}={$encoded}\n";
        }

        $temporary = $path.'.installer-'.bin2hex(random_bytes(8));
        if (file_put_contents($temporary, $contents, LOCK_EX) === false || ! @rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('Unable to safely update the environment file.');
        }
    }

    private function encode(mixed $value): string
    {
        $value = (string) $value;

        return $value === '' ? '' : '"'.addcslashes($value, '\\"').'"';
    }
}
