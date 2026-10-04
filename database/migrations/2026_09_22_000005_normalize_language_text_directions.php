<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('languages')
            ->whereRaw('LOWER(text_direction) = ?', ['ltr'])
            ->update(['text_direction' => 'LTR']);

        DB::table('languages')
            ->whereRaw('LOWER(text_direction) = ?', ['rtl'])
            ->update(['text_direction' => 'RTL']);
    }

    public function down(): void
    {
        // Direction values are intentionally normalized to uppercase.
    }
};
