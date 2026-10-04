<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('staff_name');
            $table->date('date');
            $table->string('check_in')->nullable();
            $table->string('check_out')->nullable();
            $table->decimal('hours', 5, 2)->nullable();
            $table->string('status')->default('Present');   // Present | Leave | Absent
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
    }
};
