<?php

namespace Modules\Installer\Services;

class RequirementChecker
{
    public function checks(): array
    {
        $checks = [['label' => 'PHP 8.2 or newer', 'passed' => version_compare(PHP_VERSION, '8.2.0', '>=')]];
        foreach (config('installer.required_extensions', []) as $extension) {
            $checks[] = ['label' => "PHP extension: {$extension}", 'passed' => extension_loaded($extension)];
        }
        foreach (config('installer.writable_paths', []) as $path) {
            $checks[] = ['label' => "Writable: {$path}", 'passed' => is_writable(base_path($path))];
        }

        return $checks;
    }

    public function passes(): bool
    {
        return collect($this->checks())->every(fn (array $check) => $check['passed']);
    }
}
