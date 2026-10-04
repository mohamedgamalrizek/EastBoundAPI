<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Day-by-day plan for a tour package. The public package page used to invent
 * this from a fixed five-item array, so every package showed the same days.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->unsignedInteger('day_number')->default(1);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['package_id', 'day_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_itineraries');
    }
};
