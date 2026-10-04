<?php

namespace Modules\Saas\Database\Seeders;

use App\Models\Backend\Setting;
use Illuminate\Database\Seeder;
use App\Repositories\Upload\UploadInterface;

/**
 * Seeds a fresh tenant's General Settings inside the TENANT database.
 *
 * MUST run in tenant context (e.g. inside $tenant->run(...)) so that the
 * Setting rows and the copied logo/favicon Upload rows land in the tenant's
 * own DB. Branding (name/email) is pulled from the current tenant when
 * available, so each workspace starts pre-branded instead of blank.
 *
 * Idempotent: re-running updates the same keys instead of duplicating them.
 */
class TenantSettingSeeder extends Seeder
{
    private $uploadRepo;

    public function __construct(UploadInterface $uploadRepo)
    {
        $this->uploadRepo = $uploadRepo;
    }

    public function run(): void
    {
        // Pull branding from the active tenant when present (set by $tenant->run()).
        $tenant = function_exists('tenant') ? tenant() : null;
        $name   = $tenant->name  ?? 'New Workspace';
        $email  = $tenant->email ?? 'info@example.com';

        // Copy default logo/favicon into the tenant's uploads table. Files are
        // written to the shared public/uploads dir; the Upload ROWS are isolated
        // per tenant, so each tenant points at its own (copied) asset.
        $logoId    = $this->uploadRepo->uploadSeederByPath('backend/assets/img/logo/flow-logo.png');
        $faviconId = $this->uploadRepo->uploadSeederByPath('backend/assets/img/logo/flow-favicon.png');

        $settings = [
            'name'              => $name,
            'email'             => $email,
            'phone'             => '',
            'copyright'         => 'All rights reserved.',
            'paginate_value'    => '10',
            'date_format'       => 'M d, Y',
            'time_format'       => 'h:i a',

            'light_theme_logo'  => $logoId,
            'dark_theme_logo'   => $logoId,
            'favicon'           => $faviconId,
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
