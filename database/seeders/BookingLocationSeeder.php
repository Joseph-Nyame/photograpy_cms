<?php

namespace Database\Seeders;

use App\Models\BookingLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Accra, Ghana', 'currency' => 'GHS', 'active' => true, 'sort' => 1],
            ['name' => 'Kumasi, Ghana', 'currency' => 'GHS', 'active' => true, 'sort' => 2],
            ['name' => 'Cape Coast, Ghana', 'currency' => 'GHS', 'active' => true, 'sort' => 3],
            ['name' => 'London, United Kingdom', 'currency' => 'GBP', 'active' => false, 'sort' => 4],
            ['name' => 'New York, United States', 'currency' => 'USD', 'active' => false, 'sort' => 5],
            ['name' => 'Lagos, Nigeria', 'currency' => 'NGN', 'active' => false, 'sort' => 6],
        ];

        foreach ($locations as $location) {
            $model = BookingLocation::query()->firstOrNew(['name' => $location['name']]);
            $model->fill($location);

            if (! $model->exists) {
                $model->uuid = (string) Str::uuid();
            }

            $model->save();
        }
    }
}
