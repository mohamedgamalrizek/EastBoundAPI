<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travelers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('name');
            $table->string('relation');                 // Self | Spouse | Child | Parent
            $table->string('passport_no')->nullable();
            $table->string('nationality')->nullable();
            $table->date('dob')->nullable();
            $table->string('status')->default('Active'); // Active | Inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travelers');
    }
};
