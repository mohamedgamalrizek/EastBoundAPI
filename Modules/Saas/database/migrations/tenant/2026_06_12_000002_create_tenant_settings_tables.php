<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-tenant settings + uploads. These live in EACH tenant's own database so
 * every workspace owns its General Settings (name, logo, favicon, mail, etc.)
 * fully isolated from the central platform and from other tenants.
 *
 * Because DatabaseTenancyBootstrapper swaps the default connection to the
 * tenant DB inside tenant context, the existing App\Models\Backend\Setting and
 * App\Models\Upload models — and the settings()/logo()/favicon() helpers —
 * read/write these tenant tables automatically, no per-model connection needed.
 *
 * Schema mirrors the central settings/uploads tables exactly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->string('original')->nullable();
            $table->enum('type', ['image', 'file'])->nullable();
            $table->string('image_one')->nullable();
            $table->string('image_two')->nullable();
            $table->string('image_three')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
        Schema::dropIfExists('settings');
    }
};
