<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->nullable() ->constrained('packages')->nullOnDelete();
            $table->string('package_title');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('seats')->default(0);
            $table->integer('booked')->default(0);
            $table->enum('status', ['open', 'full', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_schedules');
    }
};
