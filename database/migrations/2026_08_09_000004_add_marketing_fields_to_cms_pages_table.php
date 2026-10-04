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
            $table->string('promo_badge')->nullable()->after('story_badges');
            $table->string('promo_title')->nullable()->after('promo_badge');
            $table->string('promo_text', 500)->nullable()->after('promo_title');
            $table->string('promo_link')->nullable()->after('promo_text');
            $table->string('stat_travelers')->nullable()->after('promo_link');
            $table->string('stat_destinations')->nullable()->after('stat_travelers');
            $table->string('stat_visa_success')->nullable()->after('stat_destinations');
            $table->string('stat_experience')->nullable()->after('stat_visa_success');
        });

        $settings = DB::table('settings')
            ->whereIn('key', [
                'mega_promo_badge',
                'mega_promo_title',
                'mega_promo_text',
                'mega_promo_link',
                'stat_travelers',
                'stat_destinations',
                'stat_visa_success',
                'stat_experience',
            ])
            ->pluck('value', 'key');

        $homeFields = [
            'promo_badge' => $settings['mega_promo_badge'] ?? 'Hot Deal',
            'promo_title' => $settings['mega_promo_title'] ?? 'Umrah Packages',
            'promo_text' => $settings['mega_promo_text'] ?? 'Premium packages from BDT 145,000 - limited seats.',
            'promo_link' => $settings['mega_promo_link'] ?? '/umrah',
            'stat_travelers' => $settings['stat_travelers'] ?? '12K+',
            'stat_destinations' => $settings['stat_destinations'] ?? '220+',
            'stat_visa_success' => $settings['stat_visa_success'] ?? '98%',
            'stat_experience' => $settings['stat_experience'] ?? '11 yrs',
            'updated_at' => now(),
        ];

        $aboutFields = [
            'stat_travelers' => $settings['stat_travelers'] ?? '12K+',
            'stat_destinations' => $settings['stat_destinations'] ?? '220+',
            'stat_visa_success' => $settings['stat_visa_success'] ?? '98%',
            'stat_experience' => $settings['stat_experience'] ?? '11 yrs',
            'updated_at' => now(),
        ];

        if (DB::table('cms_pages')->where('slug', '/')->exists()) {
            DB::table('cms_pages')->where('slug', '/')->update($homeFields);
        } else {
            DB::table('cms_pages')->insert($homeFields + [
                'title' => 'Home',
                'slug' => '/',
                'status' => 'published',
                'created_at' => now(),
            ]);
        }

        DB::table('cms_pages')->where('slug', 'about-us')->update($aboutFields);
    }

    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn([
                'promo_badge',
                'promo_title',
                'promo_text',
                'promo_link',
                'stat_travelers',
                'stat_destinations',
                'stat_visa_success',
                'stat_experience',
            ]);
        });
    }
};
