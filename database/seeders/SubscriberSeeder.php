<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscriber;

class SubscriberSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // email, status, source, subscribed days ago
            ['rashed.karim@gmail.com',   'subscribed',   'home',        45],
            ['nusrat.j@gmail.com',       'subscribed',   'home',        30],
            ['tanvir.a@yahoo.com',       'subscribed',   'blog',        21],
            ['sonia.rahman@gmail.com',   'subscribed',   'packages',    14],
            ['david.costa@outlook.com',  'subscribed',   'footer',      10],
            ['mahin.alam@yahoo.com',     'subscribed',   'footer',      7],
            ['farzana.akter@gmail.com',  'subscribed',   'blog',        3],
            ['old.reader@gmail.com',     'unsubscribed', 'home',        180],
        ];

        foreach ($rows as $r) {
            Subscriber::updateOrCreate(
                ['email' => $r[0]],
                [
                    'status'        => $r[1],
                    'source'        => $r[2],
                    'subscribed_at' => now()->subDays($r[3]),
                ]
            );
        }
    }
}
