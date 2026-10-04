<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('staff_name');
            $table->string('leave_type');                   // Casual | Sick | Annual
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('days');
            $table->string('reason')->nullable();
            $table->string('status')->default('Pending');   // Pending | Approved | Rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
