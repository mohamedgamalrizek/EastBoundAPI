<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_destinations', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug')->unique();
            $table->string('headline')->nullable(); $table->text('description')->nullable();
            $table->longText('about')->nullable(); $table->string('image', 500)->nullable();
            $table->string('meta_title')->nullable(); $table->text('meta_description')->nullable();
            $table->boolean('active')->default(false); $table->unsignedInteger('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('website_cities', function (Blueprint $table) {
            $table->id(); $table->foreignId('destination_id')->constrained('website_destinations')->restrictOnDelete();
            $table->string('name'); $table->string('slug'); $table->unique(['destination_id', 'slug']);
            $table->string('headline')->nullable(); $table->text('description')->nullable();
            $table->longText('about')->nullable(); $table->string('image', 500)->nullable();
            $table->string('meta_title')->nullable(); $table->text('meta_description')->nullable();
            $table->boolean('active')->default(false); $table->unsignedInteger('sort_order')->default(0); $table->timestamps();
        });
        Schema::table('packages', function (Blueprint $table) {
            $table->foreignId('website_destination_id')->nullable()->constrained('website_destinations')->restrictOnDelete();
            $table->string('website_slug')->nullable()->unique();
            $table->boolean('website_published')->default(false);
            $table->string('website_type')->default('package');
            $table->string('website_full_title')->nullable();
            $table->boolean('website_featured')->default(false);
            $table->string('website_tag')->nullable();
            $table->text('website_why_choose')->nullable(); $table->text('website_travel_tips')->nullable();
            $table->text('website_optional_excursions')->nullable();
            $table->string('website_start_point')->nullable(); $table->string('website_duration_hours')->nullable();
        });
        Schema::create('package_website_city', function (Blueprint $table) {
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('website_city_id')->constrained('website_cities')->restrictOnDelete();
            $table->primary(['package_id', 'website_city_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('package_website_city');
        Schema::table('packages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('website_destination_id');
            $table->dropColumn(['website_slug', 'website_published', 'website_type', 'website_full_title', 'website_featured', 'website_tag', 'website_why_choose', 'website_travel_tips', 'website_optional_excursions', 'website_start_point', 'website_duration_hours']);
        });
        Schema::dropIfExists('website_cities'); Schema::dropIfExists('website_destinations');
    }
};
