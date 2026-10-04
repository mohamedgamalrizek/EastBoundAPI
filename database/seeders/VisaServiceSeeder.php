<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VisaApplication;
use App\Models\VisaService;

/**
 * The visa catalogue rendered on /visa-services. Migrated out of the old
 * hardcoded blade array so the agency can price and publish its own list.
 */
class VisaServiceSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['United Arab Emirates', '🇦🇪', 'Tourist', '3–5 days',  9000,  1, true],
            ['Saudi Arabia',         '🇸🇦', 'Umrah',   '5–7 days',  12000, 2, true],
            ['Schengen (Europe)',    '🇪🇺', 'Tourist', '15 days',   18000, 3, true],
            ['Malaysia',             '🇲🇾', 'Tourist', '3 days',    7500,  4, false],
            ['Thailand',             '🇹🇭', 'Tourist', '4 days',    6000,  5, false],
            ['Turkey',               '🇹🇷', 'Tourist', 'e-Visa · 2 days', 5500, 6, false],
            ['United States',        '🇺🇸', 'Business','B1/B2 · 4 weeks', 25000, 7, false],
            ['United Kingdom',       '🇬🇧', 'Tourist', 'Visitor · 3 weeks', 22000, 8, false],
            ['Singapore',            '🇸🇬', 'Tourist', '5 days',    8500,  9, false],
            ['Canada',               '🇨🇦', 'Student', '8–12 weeks', 30000, 10, false],
        ];

        $requirements = implode("\n", [
            'Passport valid for at least 6 months',
            'Two recent passport-size photographs',
            'Completed application form',
            'Six months of bank statements',
            'Employment or business documents',
            'Confirmed flight and hotel booking',
        ]);

        foreach ($rows as $r) {
            VisaService::updateOrCreate(
                ['country' => $r[0], 'visa_type' => $r[2]],
                [
                    'flag'            => $r[1],
                    'processing_time' => $r[3],
                    'fee'             => $r[4],
                    'sort_order'      => $r[5],
                    'is_featured'     => $r[6],
                    'requirements'    => $requirements,
                    'status'          => 'active',
                ]
            );
        }

        $this->linkApplicationsToCatalogue();
    }

    /**
     * Point existing applications at the catalogue entry they were sold from.
     *
     * Applications record country + visa type as loose strings; where that pair
     * identifies exactly one service the link is safe to make automatically.
     * Ambiguous pairs are left for staff to resolve rather than guessed at.
     */
    private function linkApplicationsToCatalogue(): void
    {
        foreach (VisaService::all() as $service) {
            $twins = VisaService::where('country', $service->country)
                ->where('visa_type', $service->visa_type)
                ->count();

            if ($twins !== 1) {
                continue;
            }

            // Only copy a split the catalogue actually records. Writing 0/0
            // would look like a real split that does not add up to the fee.
            $hasSplit = (float) $service->govt_fee > 0 || (float) $service->service_fee > 0;

            VisaApplication::whereNull('visa_service_id')
                ->where('country', $service->country)
                ->where('visa_type', $service->visa_type)
                ->update([
                    'visa_service_id' => $service->id,
                    'govt_fee'        => $hasSplit ? $service->govt_fee : null,
                    'service_fee'     => $hasSplit ? $service->service_fee : null,
                ]);
        }
    }
}
