<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hajj_packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_no')->unique();
            $table->string('title');
            $table->string('type');                       // Hajj | Umrah
            $table->integer('duration_days');
            $table->decimal('price', 10, 2);
            $table->integer('seats');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hajj_packages');
    }
};
