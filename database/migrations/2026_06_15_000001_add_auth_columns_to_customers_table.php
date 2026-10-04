<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds authentication columns to the customers table so a Customer can log in
 * to the FLOW mobile app (B2C). The web ERP keeps managing customers as
 * before; these columns are only populated when a customer self-registers or
 * is given app access.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('password')->nullable()->after('phone');
            $table->timestamp('email_verified_at')->nullable()->after('password');
            $table->string('otp')->nullable()->after('email_verified_at')->comment('Login/verify OTP code');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['password', 'email_verified_at', 'otp', 'otp_expires_at', 'remember_token']);
        });
    }
};
