<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visa_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('package_id')->nullable()->index();
            // Which catalogue entry was sold. No foreign key: `visa_services`
            // is created later, and an application may pre-date the catalogue.
            $table->unsignedBigInteger('visa_service_id')->nullable()->index();
            $table->string('application_no')->unique();
            $table->string('applicant_name');
            $table->string('country');
            $table->string('visa_type');                       // Tourist | Business | Umrah | Student
            // Fee split captured at sale time, so reporting is not rewritten
            // when the catalogue price later changes.
            $table->decimal('govt_fee', 12, 2)->nullable();
            $table->decimal('service_fee', 12, 2)->nullable();
            $table->date('applied_date')->nullable();
            $table->date('appointment_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('documents_status')->default('Pending'); // Pending | Submitted | Verified
            $table->string('status')->default('Processing');   // Processing | In Review | Approved | Rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visa_applications');
    }
};
