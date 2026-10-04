<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sliders drive the public home hero. They previously had no image or
 * call-to-action, so the hero copy lived in the blade template.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->string('image')->nullable()->after('image_label');
            $table->string('badge')->nullable()->after('image');
            $table->string('cta_text')->nullable()->after('badge');
            $table->string('cta_link')->nullable()->after('cta_text');
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn(['image', 'badge', 'cta_text', 'cta_link']);
        });
    }
};
