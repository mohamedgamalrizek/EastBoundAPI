<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('holder_name');
            $table->string('passport_no')->unique();
            $table->string('nationality')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('Valid'); // Valid | Expiring | Expired
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passports');
    }
};
