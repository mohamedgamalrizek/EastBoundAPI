<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('interest')->nullable();
            $table->enum('source', ['Facebook', 'Website', 'Referral', 'WhatsApp', 'Instagram', 'Walk-in'])->default('Website');
            $table->decimal('value', 12, 2)->default(0);
            $table->enum('stage', ['New', 'Contacted', 'Proposal', 'Negotiation', 'Won', 'Lost'])->default('New');
            $table->string('owner')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
