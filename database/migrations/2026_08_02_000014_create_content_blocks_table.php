<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The small icon+title+text cards repeated across the public site — the home
 * "what we do" and "why us" grids, the about values, the visa steps, support
 * topics, agent benefits and the Hajj/Umrah inclusion lists.
 *
 * They all share one shape, so one table with a `section` key covers them
 * instead of eight near-identical tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('section')->index();      // e.g. home_services
            $table->string('icon')->nullable();      // Font Awesome class
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('url')->nullable();       // path, full URL, or a route name
            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
