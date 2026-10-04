<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The visa *catalogue* the public site advertises (country, type, fee,
 * turnaround). Distinct from `visa_applications`, which are submitted cases.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visa_services', function (Blueprint $table) {
            $table->id();
            $table->string('country');
            $table->string('flag')->nullable();               // emoji or short code
            $table->string('visa_type')->default('Tourist');  // Tourist | Business | Student | Work | Umrah
            $table->string('processing_time')->nullable();    // e.g. "3–5 days"
            $table->decimal('fee', 12, 2)->default(0);
            $table->text('requirements')->nullable();         // one per line
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('status')->default('active');      // active | inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visa_services');
    }
};
