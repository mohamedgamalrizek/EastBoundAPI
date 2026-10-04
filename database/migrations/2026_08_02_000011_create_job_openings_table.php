<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vacancies listed on the public careers page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->string('employment_type')->default('Full-time'); // Full-time | Part-time | Contract | Seasonal | Internship
            $table->text('description')->nullable();
            $table->date('closing_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('status')->default('active');      // active | inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_openings');
    }
};
