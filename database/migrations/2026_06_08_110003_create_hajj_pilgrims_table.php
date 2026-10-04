<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hajj_pilgrims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hajj_package_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('pilgrim_no')->unique();
            $table->string('name');
            $table->string('passport_no');
            $table->string('package_title');
            $table->string('group_name')->nullable();
            $table->string('payment_status')->default('Pending');
            $table->string('document_status')->default('Pending');
            $table->string('status')->default('Registered');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hajj_pilgrims');
    }
};
