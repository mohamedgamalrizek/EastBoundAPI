<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleCategory;

/**
 * Extra vehicle categories beyond the 4 baseline ones the
 * create_vehicle_categories_table migration seeds directly (Sedan, SUV,
 * Microbus, Coach — kept in the migration since those must exist in every
 * environment, demo or not, for the booking forms to have any option at all).
 * This seeder only adds sample variety for a fuller demo catalogue.
 */
class VehicleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Hatchback',    5],
            ['Noah / Hiace', 6],
            ['Pickup Van',   7],
            ['Premium SUV',  8],
            ['Mini Coach',   9],
            ['Luxury Car',   10],
        ];

        foreach ($rows as [$name, $sortOrder]) {
            VehicleCategory::updateOrCreate(
                ['name' => $name],
                ['sort_order' => $sortOrder, 'status' => 'active']
            );
        }
    }
}
