<?php

namespace Modules\Saas\Database\Seeders;

use App\Models\Backend\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * Creates one `settings` row per SaaS payment-gateway credential.
 *
 * The platform's gateway credentials are database values, not .env entries, so
 * the owner can go live from the panel without a redeploy and no secret sits in
 * a file that a copied backup would expose.
 *
 * This seeder is only reached through SaasDatabaseSeeder, which DatabaseSeeder
 * calls behind `config('saas.enabled')` — a single-company install never gets
 * these rows, because it never uses these gateways. Its own gateway settings
 * are seeded separately under the bare `<gateway>_<field>` keys.
 */
class PaymentSettingSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [];

        foreach (config('payment.fields', []) as $gateway => $fields) {
            foreach ($fields as $field) {
                $rows[] = [
                    'key' => "saas_{$gateway}_{$field}",
                    // Sandbox on by default: a fresh platform cannot take a
                    // real payment until the owner deliberately turns it off.
                    'value' => $field === 'sandbox' ? '1' : null,
                ];
            }

            // Off until the owner has entered credentials and enabled it.
            $rows[] = ['key' => "saas_{$gateway}_status", 'value' => '0'];
        }

        // firstOrCreate, not create: re-running on a live platform adds any
        // newly-introduced gateway without overwriting credentials already set.
        foreach ($rows as $row) {
            Setting::firstOrCreate(['key' => $row['key']], ['value' => $row['value']]);
        }

        Cache::forget(tenant_cache_prefix() . 'settings');
    }
}
