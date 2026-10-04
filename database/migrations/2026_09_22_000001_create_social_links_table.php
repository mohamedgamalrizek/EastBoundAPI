<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon', 100);
            $table->string('url', 2048);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        // Preserve existing General Settings links on upgraded installs.
        if (Schema::hasTable('settings')) {
            $icons = [
                'facebook_url' => ['Facebook', 'fa-facebook-f'],
                'instagram_url' => ['Instagram', 'fa-instagram'],
                'linkedin_url' => ['LinkedIn', 'fa-linkedin-in'],
                'youtube_url' => ['YouTube', 'fa-youtube'],
                'twitter_url' => ['X / Twitter', 'fa-x-twitter'],
            ];

            foreach ($icons as $key => [$name, $icon]) {
                $url = DB::table('settings')->where('key', $key)->value('value');
                if (filled($url)) {
                    DB::table('social_links')->insert([
                        'name' => $name,
                        'icon' => $icon,
                        'url' => $url,
                        'sort_order' => count(DB::table('social_links')->get()),
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
