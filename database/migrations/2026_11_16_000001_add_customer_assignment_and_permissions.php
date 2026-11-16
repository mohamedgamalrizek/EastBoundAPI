<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('customers', 'assigned_to')) Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
        });
        (new \Database\Seeders\CustomerAccessPermissionSeeder)->run();
    }

    public function down(): void
    {
        // Preserve assignments and granted permissions when rolling back.
    }
};
