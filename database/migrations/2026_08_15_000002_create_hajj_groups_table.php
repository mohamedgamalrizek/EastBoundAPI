<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hajj groups as records of their own.
 *
 * Until now a "group" was only whatever text somebody typed into a pilgrim's
 * `group_name`, which meant the office had to remember and retype the name
 * exactly, a typo silently created a second group, and a group could not be
 * set up before the first pilgrim was put in it.
 *
 * The pilgrim keeps its `group_name` string — every other screen, the API and
 * the search filter read it — but this table is the list the office picks
 * from, so names are chosen rather than typed, and an empty group can exist
 * while it is being filled.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hajj_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('leader')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Whatever groups the pilgrims are already in become the first entries,
        // so nothing disappears from the Groups screen after this runs.
        $existing = DB::table('hajj_pilgrims')
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->distinct()
            ->pluck('group_name');

        foreach ($existing as $name) {
            DB::table('hajj_groups')->insert([
                'name'       => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hajj_groups');
    }
};
