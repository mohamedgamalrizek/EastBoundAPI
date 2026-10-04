<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_guide_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_guide_id')->constrained('tour_guides')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('tour_guide_assignment_id')->nullable()->constrained('tour_guide_assignments')->nullOnDelete();
            $table->unsignedTinyInteger('rating'); // 1..5
            $table->text('comment')->nullable();
            $table->timestamps();

            // One verdict per customer per guide per tour.
            $table->unique(['tour_guide_id', 'customer_id', 'tour_guide_assignment_id'], 'guide_customer_assignment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_guide_ratings');
    }
};
