<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    /**
     * Seed demo listings (mirrors Flutter mock data) for local development.
     */
    public function run(): void
    {
        $amenityIds = Amenity::query()->pluck('id', 'slug')->all();

        $venues = [
            [
                'slug' => 'mcd-kepong',
                'name' => "McDonald's Kepong",
                'subtitle' => 'Western',
                'address' => 'No. 1 & 2, Jalan 54, Desa Jaya, 52100 Kepong, Kuala Lumpur',
                'contact_number' => '+60 3-6276 7457',
                'latitude' => 3.21351,
                'longitude' => 101.63205,
                'postal_code' => '52100',
                'rating' => 4.5,
                'operating_hours' => ['Open 24 hours'],
                'images' => [],
            ],
            [
                'slug' => 'pelita-kepong',
                'name' => 'Pelita Nasi Kandar (Kepong)',
                'subtitle' => 'Malaysian',
                'address' => '49, Jalan Kepong, 52000 Kuala Lumpur',
                'contact_number' => '+60 3-6273 8822',
                'latitude' => 3.2140,
                'longitude' => 101.6365,
                'postal_code' => '52000',
                'rating' => 4.5,
                'operating_hours' => ['Mon – Sun  06:00 – 02:00'],
                'images' => [],
            ],
            [
                'slug' => 'dimsum-kepong',
                'name' => 'Morning Dim Sum (Kepong)',
                'subtitle' => 'Chinese',
                'address' => '38, Jalan Metro Perdana 1, Kepong Metropolitan, 52100 Kuala Lumpur',
                'contact_number' => '+60 3-6274 7711',
                'latitude' => 3.2165,
                'longitude' => 101.6380,
                'postal_code' => '52100',
                'rating' => 4.2,
                'operating_hours' => [
                    'Tue – Sun  07:00 – 14:00',
                    'Mon  Closed',
                ],
                'images' => [],
            ],
            [
                'slug' => 'kfc-kepong',
                'name' => 'KFC Kepong',
                'subtitle' => 'Western',
                'address' => 'No. 3, Jalan 54, Desa Jaya, 52100 Kepong, Kuala Lumpur',
                'contact_number' => '+60 3-6275 5566',
                'latitude' => 3.2138,
                'longitude' => 101.6315,
                'postal_code' => '52100',
                'rating' => 4.1,
                'operating_hours' => ['Mon – Sun  10:00 – 23:00'],
                'images' => [],
            ],
            [
                'slug' => 'simple-cafe-kepong',
                'name' => 'Simple Cafe Kepong',
                'subtitle' => 'Cafe',
                'address' => '12, Jalan Kepong, 52000 Kuala Lumpur',
                'contact_number' => '+60 3-6271 0099',
                'latitude' => 3.2120,
                'longitude' => 101.6340,
                'postal_code' => '52000',
                'rating' => 4.6,
                'operating_hours' => [
                    'Mon – Fri  09:00 – 22:00',
                    'Sat – Sun  09:00 – 23:00',
                ],
                'images' => [],
            ],
            [
                'slug' => 'siraj-kepong',
                'name' => 'Sirajudeen Nasi Kandar (Kepong)',
                'subtitle' => 'Malaysian',
                'address' => '15, Jalan Kepong, 52000 Kuala Lumpur',
                'contact_number' => '+60 3-6272 3344',
                'latitude' => 3.2155,
                'longitude' => 101.6370,
                'postal_code' => '52000',
                'rating' => 4.4,
                'operating_hours' => ['Mon – Sun  24 hours'],
                'images' => [],
            ],
            [
                'slug' => 'caro-kepong',
                'name' => 'Caro Caro Cafe (Kepong)',
                'subtitle' => 'Cafe',
                'address' => '22, Jalan Metro Perdana, Kepong Metropolitan, 52100 Kuala Lumpur',
                'contact_number' => '+60 3-6270 7766',
                'latitude' => 3.2105,
                'longitude' => 101.6330,
                'postal_code' => '52100',
                'rating' => 4.0,
                'operating_hours' => [
                    'Wed – Mon  10:00 – 21:00',
                    'Tue  Closed',
                ],
                'images' => [],
            ],
            [
                'slug' => '7eleven-kepong',
                'name' => '7-Eleven (Kepong Metropolitan)',
                'subtitle' => 'Convenience Store',
                'address' => 'Lot G-08, Ground Floor, Metropolitan Batu, Jalan Ipoh, 52100 Kuala Lumpur',
                'contact_number' => '+60 3-6278 1122',
                'latitude' => 3.2172,
                'longitude' => 101.6395,
                'postal_code' => '52100',
                'rating' => 4.5,
                'operating_hours' => ['Open 24 hours'],
                'images' => [
                    ['url' => 'https://placehold.co/800x500/png?text=7-Eleven+1', 'sort_order' => 0],
                    ['url' => 'https://placehold.co/800x500/png?text=7-Eleven+2', 'sort_order' => 1],
                ],
                'amenity_slugs' => [
                    'free-wifi',
                    'intl-cards',
                    'sim-topup',
                    'print',
                    'rental-board',
                    'parcel',
                ],
            ],
        ];

        foreach ($venues as $venue) {
            $restaurant = Restaurant::updateOrCreate(
                ['slug' => $venue['slug']],
                [
                    'name' => $venue['name'],
                    'subtitle' => $venue['subtitle'],
                    'address' => $venue['address'],
                    'contact_number' => $venue['contact_number'],
                    'latitude' => $venue['latitude'],
                    'longitude' => $venue['longitude'],
                    'postal_code' => $venue['postal_code'],
                    'rating' => $venue['rating'],
                    'operating_hours' => $venue['operating_hours'],
                    'qr_data' => null,
                    'is_active' => true,
                ],
            );

            $slugs = $venue['amenity_slugs'] ?? [];
            if ($slugs !== []) {
                $ids = array_values(array_filter(array_map(
                    fn (string $slug) => $amenityIds[$slug] ?? null,
                    $slugs,
                )));
                $restaurant->amenities()->sync($ids);
            }

            $restaurant->images()->delete();
            foreach ($venue['images'] as $image) {
                $restaurant->images()->create($image);
            }
        }
    }
}
