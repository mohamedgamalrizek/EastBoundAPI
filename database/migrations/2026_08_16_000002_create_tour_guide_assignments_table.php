<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_guide_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_guide_id')->constrained('tour_guides')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            // Optional link to a concrete departure when one exists.
            $table->foreignId('tour_schedule_id')->nullable()->constrained('tour_schedules')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tour_guide_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_guide_assignments');
    }
};
