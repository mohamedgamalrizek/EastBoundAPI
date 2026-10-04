<?php

use App\Enums\TodoStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->longText('note')->nullable();
            $table->foreignId('file_id')->nullable()->constrained('uploads')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', array_column(TodoStatus::cases(), 'value'));

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
