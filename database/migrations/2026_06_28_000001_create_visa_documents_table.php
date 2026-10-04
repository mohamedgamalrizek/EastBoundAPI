<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visa_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visa_application_id')
                ->constrained('visa_applications')
                ->cascadeOnDelete();
            $table->string('document_type');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->string('status')->default('Submitted');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visa_documents');
    }
};
