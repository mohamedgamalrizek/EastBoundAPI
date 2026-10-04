<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->string('breadcrumb_label')->nullable()->after('body');
            $table->string('hero_subtitle', 500)->nullable()->after('breadcrumb_label');
            $table->string('section_icon')->nullable()->after('hero_subtitle');
            $table->string('section_eyebrow')->nullable()->after('section_icon');
            $table->string('section_heading')->nullable()->after('section_eyebrow');
            $table->string('form_title')->nullable()->after('section_heading');
            $table->string('form_intro', 500)->nullable()->after('form_title');
            $table->string('form_name_label')->nullable()->after('form_intro');
            $table->string('form_name_placeholder')->nullable()->after('form_name_label');
            $table->string('form_phone_label')->nullable()->after('form_name_placeholder');
            $table->string('form_phone_placeholder')->nullable()->after('form_phone_label');
            $table->string('form_email_label')->nullable()->after('form_phone_placeholder');
            $table->string('form_email_placeholder')->nullable()->after('form_email_label');
            $table->string('form_notes_label')->nullable()->after('form_email_placeholder');
            $table->string('form_notes_placeholder', 500)->nullable()->after('form_notes_label');
            $table->string('form_submit_button')->nullable()->after('form_notes_placeholder');
            $table->string('success_message', 500)->nullable()->after('form_submit_button');
        });
    }

    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn([
                'breadcrumb_label',
                'hero_subtitle',
                'section_icon',
                'section_eyebrow',
                'section_heading',
                'form_title',
                'form_intro',
                'form_name_label',
                'form_name_placeholder',
                'form_phone_label',
                'form_phone_placeholder',
                'form_email_label',
                'form_email_placeholder',
                'form_notes_label',
                'form_notes_placeholder',
                'form_submit_button',
                'success_message',
            ]);
        });
    }
};
