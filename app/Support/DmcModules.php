<?php

namespace App\Support;

use Illuminate\Support\Str;

class DmcModules
{
    public const EXCLUDED = [
        'visa', 'hajj', 'flight', 'insurance', 'student_service', 'medical_tour',
        'corporate_travel', 'event_tour', 'coupon', 'review',
    ];

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
            'flight.*', 'insurance.*', 'medical-tour.*', 'corporate-travel.*', 'event-tour.*',
            'coupon.*', 'review.*', 'newsletter-subscriber.*',
            'report.visa', 'report.flight', 'sup.visa', 'cms.visa-service.*', 'cms.flight-route.*',
            'crm.leads.convert.flight', 'crm.leads.store.flight',
            'settings.flight-api.*',
        ], $route ?? '');
    }
}
