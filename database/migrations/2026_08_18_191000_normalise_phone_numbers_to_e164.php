<?php

use App\Services\Contact\PhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bring existing phone numbers onto one canonical format.
 *
 * `customers.phone` is unique and doubles as a login identifier, but rows
 * written before this migration hold whatever was typed — mostly bare national
 * numbers. Left alone, the same person can hold several accounts and a
 * customer who signs in with a different spelling of their own number is
 * refused.
 *
 * Numbers already carrying a country code are left as they are. Bare national
 * numbers take the install's default dial code, which is the only assumption
 * available and is configurable before this ever runs.
 *
 * Rows that would collide after normalisation are deliberately NOT merged —
 * merging customer records is a business decision, not a migration's. They are
 * left untouched so the install still boots and the duplicates stay visible.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['customers', 'users'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'phone')) {
                continue;
            }

            $seen = DB::table($table)->whereNotNull('phone')->pluck('phone')->all();
            $seen = array_flip($seen);

            DB::table($table)->whereNotNull('phone')->where('phone', '!=', '')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table, &$seen) {
                    foreach ($rows as $row) {
                        $normalised = PhoneNumber::e164($row->phone);

                        if ($normalised === '' || $normalised === $row->phone) {
                            continue;
                        }

                        // Would collide with another row — leave both alone.
                        if (isset($seen[$normalised])) {
                            continue;
                        }

                        DB::table($table)->where('id', $row->id)->update(['phone' => $normalised]);
                        unset($seen[$row->phone]);
                        $seen[$normalised] = true;
                    }
                });
        }
    }

    public function down(): void
    {
        // Irreversible by design: the original spelling is not recorded, and
        // guessing it back would be worse than leaving the canonical form.
    }
};
