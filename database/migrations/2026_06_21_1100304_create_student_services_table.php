<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_services', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->string('university')->nullable();
            $table->string('country')->nullable();
            $table->string('service_type');
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_services');
    }
};
