<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The public blog listing/detail pages used to render a hardcoded array of
 * posts because `blogs` only stored a title + slug. These columns let the CMS
 * own the real content.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('category')->nullable()->after('author');
            $table->string('image')->nullable()->after('category');
            $table->string('excerpt', 500)->nullable()->after('image');
            $table->longText('body')->nullable()->after('excerpt');
            $table->unsignedInteger('read_minutes')->default(4)->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['category', 'image', 'excerpt', 'body', 'read_minutes']);
        });
    }
};
