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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('title');
            $table->string('destination');
            $table->string('category')->default('Tour'); // Tour, Beach, City, Honeymoon, Adventure
            // `price` is per traveller — bookings multiply it by head count.
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('child_price', 12, 2)->nullable();
            $table->decimal('single_supplement', 12, 2)->nullable();
            $table->unsignedInteger('duration_days')->default(1);
            $table->unsignedInteger('duration_nights')->default(0);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
