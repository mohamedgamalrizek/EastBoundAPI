<?php

namespace Database\Seeders;

use App\Enums\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('route_lists')->truncate();

        $prefixes = ['dashboard', 'home', 'admin', 'user', 'profile', 'todo', 'activity-logs', 'search'];


        $routes = Route::getRoutes()->getRoutesByMethod()['GET'];

        foreach ($routes as $route) {

            $title = str_replace(['.', '-'], ' ', $route->getName());
            $title = ucwords(preg_replace('/([a-z])([A-Z])/', '$1 $2', $title));

            $routeName  = $route->getName();
            $uri        = $route->uri();

            if ((Str::startsWith($uri, $prefixes) || Str::startsWith($routeName, 'dashboard')) && !Str::contains($uri, '{')) {

                $data = [
                    'title'     => $title,
                    'name'      => $routeName,
                    'uri'       => $uri,

                ];

                DB::table('route_lists')->insert($data);
            }
        }
    }
}
