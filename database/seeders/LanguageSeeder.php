<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Backend\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $languages = [
            ['name' => 'English', 'code' => 'en', 'icon_class' => 'flag-icon flag-icon-us', 'text_direction' => 'LTR', 'status' => Status::ACTIVE],
            ['name' => 'Bangla', 'code' => 'bn', 'icon_class' => 'flag-icon flag-icon-bd', 'text_direction' => 'LTR', 'status' => Status::ACTIVE],
            ['name' => 'Arabic', 'code' => 'ar', 'icon_class' => 'flag-icon flag-icon-ae', 'text_direction' => 'RTL', 'status' => Status::ACTIVE],
            ['name' => 'French', 'code' => 'fr', 'icon_class' => 'flag-icon flag-icon-fr', 'text_direction' => 'LTR', 'status' => Status::ACTIVE],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(['code' => $language['code']], $language);
        }
    }
}
