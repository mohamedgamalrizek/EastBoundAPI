<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gives CMS pages real content so the public legal pages (privacy, terms,
 * refund, cancellation) render from the DB instead of hardcoded blade copy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->string('meta_description', 500)->nullable()->after('slug');
            $table->longText('body')->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn(['meta_description', 'body']);
        });
    }
};
