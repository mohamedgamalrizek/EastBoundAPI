<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new \Database\Seeders\LeadPermissionSeeder)->run();
    }

    public function down(): void
    {
        // Keep assigned permissions and ownership data when rolling back.
    }
};
