<?php

namespace Database\Seeders;

use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class SocialLinkSeeder extends Seeder
{
    public function run(): void
    {
        if (SocialLink::query()->exists()) {
            return;
        }

        foreach ([
            ['facebook_url', 'Facebook', 'fa-facebook-f'],
            ['instagram_url', 'Instagram', 'fa-instagram'],
            ['linkedin_url', 'LinkedIn', 'fa-linkedin-in'],
            ['youtube_url', 'YouTube', 'fa-youtube'],
            ['twitter_url', 'X / Twitter', 'fa-x-twitter'],
        ] as $sort => [$key, $name, $icon]) {
            if (filled($url = settings($key))) {
                SocialLink::create(compact('name', 'icon', 'url') + ['sort_order' => $sort, 'status' => 'active']);
            }
        }
    }
}
