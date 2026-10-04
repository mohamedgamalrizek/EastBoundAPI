<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_tours', function (Blueprint $table) {
            $table->id();
            $table->string('patient_name');
            $table->string('destination');
            $table->string('hospital')->nullable();
            $table->string('treatment')->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            $table->string('status')->default('Inquiry');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_tours');
    }
};
