<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Seed the known amenities. The definitive list + icons should be confirmed
     * by the design/client team; these are the ones confirmed so far.
     */
    public function run(): void
    {
        $amenities = [
            ['name' => 'Free WI-FI', 'slug' => 'free-wifi', 'icon' => 'wifi'],
            ['name' => 'INTL Cards', 'slug' => 'intl-cards', 'icon' => 'credit_card'],
            ['name' => 'Sim Top-Up', 'slug' => 'sim-topup', 'icon' => 'sim_card'],
            ['name' => 'Print', 'slug' => 'print', 'icon' => 'print'],
            ['name' => 'Rental Board', 'slug' => 'rental-board', 'icon' => 'dashboard'],
            ['name' => 'Parcel', 'slug' => 'parcel', 'icon' => 'package'],
        ];

        foreach ($amenities as $index => $amenity) {
            Amenity::updateOrCreate(
                ['slug' => $amenity['slug']],
                [
                    'name' => $amenity['name'],
                    'icon' => $amenity['icon'],
                    'is_active' => true,
                    'sort_order' => $index,
                ],
            );
        }
    }
}
