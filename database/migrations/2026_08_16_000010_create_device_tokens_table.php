<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            // Customer or User — whoever is signed into the app on the device.
            $table->morphs('notifiable');
            $table->string('token', 512);
            $table->string('platform', 20)->nullable(); // android | ios
            $table->timestamps();

            // One row per device: a token re-registered by another account
            // moves to that account instead of duplicating.
            $table->unique('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
