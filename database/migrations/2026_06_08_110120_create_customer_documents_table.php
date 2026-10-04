<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('title');
            $table->string('type');                       // PDF | JPG | PNG
            $table->string('file_label')->nullable();
            $table->date('uploaded_on')->nullable();
            $table->string('status')->default('Pending'); // Verified | Pending | Rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_documents');
    }
};
