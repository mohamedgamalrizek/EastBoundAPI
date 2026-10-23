<?php

namespace App\Support;

use Illuminate\Support\Str;

class DmcModules
{
    public const EXCLUDED = ['visa', 'hajj', 'student_service'];

    public static function permissionEnabled(?string $permission): bool
    {
        foreach (self::EXCLUDED as $module) {
            if ($permission === $module || str_starts_with($permission ?? '', $module.'_')) {
                return false;
            }
        }

        return true;
    }

    public static function routeEnabled(?string $route): bool
    {
        return ! Str::is([
            'visa.*', 'hajj.*', 'student-service.*',
            'report.visa', 'sup.visa', 'cms.visa-service.*',
        ], $route ?? '');
    }
}
