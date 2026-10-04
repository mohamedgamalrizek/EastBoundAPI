<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The web signup / password-reset code had no expiry: `users.token` was set and
 * only cleared on use, so a code emailed months ago still worked. The app side
 * already expired its OTP after five minutes (`customers.otp_expires_at`); this
 * brings the two in line.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('token_expires_at')->nullable()->after('token');
        });

        // Anything already issued predates expiry and cannot be trusted.
        Schema::disableForeignKeyConstraints();
        \Illuminate\Support\Facades\DB::table('users')->whereNotNull('token')->update(['token' => null]);
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('token_expires_at');
        });
    }
};
