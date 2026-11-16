<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new \Database\Seeders\OpportunityAccessPermissionSeeder)->run();
    }

    public function down(): void
    {
        // Preserve granted permissions on rollback.
    }
};
