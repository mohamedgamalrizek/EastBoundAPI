<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->string('story_image')->nullable()->after('hero_subtitle');
            $table->string('story_eyebrow')->nullable()->after('story_image');
            $table->string('story_heading')->nullable()->after('story_eyebrow');
            $table->string('story_lead', 500)->nullable()->after('story_heading');
            $table->longText('story_body')->nullable()->after('story_lead');
            $table->string('story_badges', 500)->nullable()->after('story_body');
        });

        $settings = DB::table('settings')
            ->whereIn('key', ['about_image', 'about_story_heading', 'about_story_lead', 'about_story_body', 'about_badges'])
            ->pluck('value', 'key');

        $aboutData = [
            'title' => 'About Us',
            'slug' => 'about-us',
            'meta_description' => 'Tours, visas, flights and pilgrimages for thousands of travelers across Bangladesh and beyond.',
            'body' => null,
            'breadcrumb_label' => 'About Us',
            'hero_subtitle' => 'We make travel effortless, transparent and trustworthy.',
            'story_image' => $settings['about_image'] ?? null,
            'story_eyebrow' => 'Our story',
            'story_heading' => $settings['about_story_heading'] ?? 'Built by travel people, for travelers',
            'story_lead' => $settings['about_story_lead'] ?? 'From a single agency in Dhaka to a full travel platform - tours, visas, flights, hotels and Hajj & Umrah, all under one roof.',
            'story_body' => $settings['about_story_body'] ?? 'We combine deep local expertise with modern technology so every journey is simple to book and a joy to experience. Our mission is to make world-class travel accessible to everyone.',
            'story_badges' => $settings['about_badges'] ?? 'IATA accredited, Govt. approved Hajj agency, 24/7 support',
            'status' => 'published',
            'updated_at' => now(),
        ];

        if (DB::table('cms_pages')->where('slug', 'about-us')->exists()) {
            DB::table('cms_pages')->where('slug', 'about-us')->update($aboutData);
        } elseif (DB::table('cms_pages')->where('slug', '/about')->exists()) {
            DB::table('cms_pages')->where('slug', '/about')->update($aboutData);
        } else {
            DB::table('cms_pages')->insert($aboutData + ['created_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn([
                'story_image',
                'story_eyebrow',
                'story_heading',
                'story_lead',
                'story_body',
                'story_badges',
            ]);
        });
    }
};
