<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-tenant schema. These tables live in each tenant's OWN database
 * (tenant{uuid}), created + migrated when a tenant is provisioned. Each tenant
 * runs an isolated mini-ERP — its own staff, customers, packages and bookings.
 *
 * Run automatically by `php artisan tenants:migrate` (migration_parameters in
 * config/tenancy.php points the tenant migrator here).
 */
return new class extends Migration
{
    public function up(): void
    {
        // The tenant's own staff/users (separate from the central platform users).
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('role')->default('owner'); // owner | staff
            $table->string('status')->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('tier')->default('Silver');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('destination')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('duration_days')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('package_id')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->date('travel_date')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('users');
    }
};
