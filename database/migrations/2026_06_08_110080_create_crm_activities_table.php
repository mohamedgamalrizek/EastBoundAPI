<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->string('customer_name');
            $table->string('type');                    // followup | activity | note | communication
            $table->string('subject');
            $table->text('body')->nullable();
            $table->date('activity_date')->nullable();
            $table->string('channel')->nullable();     // Call | Email | WhatsApp | Meeting | SMS
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
    }
};
