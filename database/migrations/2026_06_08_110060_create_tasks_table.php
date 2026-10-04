<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->string('title');
            $table->string('project')->nullable();
            $table->string('priority')->default('Medium');   // Low | Medium | High
            $table->date('due_date')->nullable();
            $table->string('status')->default('Todo');        // Todo | In Progress | Done
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
