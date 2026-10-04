<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix settings values for encrypted secrets such as Google/Facebook OAuth
     * client secrets, which expand beyond 255 characters after encryption.
     */
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `settings` MODIFY COLUMN `value` TEXT NULL');
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->text('value')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `settings` MODIFY COLUMN `value` VARCHAR(255) NULL');
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->string('value')->nullable()->change();
        });
    }
};
