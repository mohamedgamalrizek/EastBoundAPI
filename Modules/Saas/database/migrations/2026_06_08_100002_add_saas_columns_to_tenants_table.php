<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! config('saas.enabled')) {
            return; // SaaS mode off → the tenants table does not exist
        }

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('email')->nullable()->after('name');
            $table->foreignId('plan_id')->nullable()->after('email')->constrained('plans')->nullOnDelete();
            $table->string('status')->default('active')->after('plan_id'); // active | suspended | pending
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn(['name', 'email', 'status']);
        });
    }
};
