<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Support\DefaultServicePackages;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'slug' => 'portrait-session',
                'name' => 'Portrait session',
                'description' => "One hour, one location, 20 edited photos delivered in a private gallery.\n60-minute shoot\nOne location in Accra\n20 edited images\nPrivate online gallery",
                'price_from' => 800,
                'duration' => '1 hour',
                'active' => true,
                'sort' => 1,
            ],
            [
                'slug' => 'event-coverage',
                'name' => 'Event coverage',
                'description' => "Up to four hours of coverage with a same-week online gallery.\nUp to 4 hours\nTwo photographers\n80+ edited images\nPrivate online gallery",
                'price_from' => 2500,
                'duration' => '4 hours',
                'active' => true,
                'sort' => 2,
            ],
            [
                'slug' => 'wedding-day',
                'name' => 'Wedding day',
                'description' => "Full-day coverage, a second shooter and a printed album.\nFull-day coverage\nTwo photographers\n400+ edited images\nPrinted 30-page album",
                'price_from' => 6000,
                'duration' => 'Full day',
                'active' => true,
                'sort' => 3,
            ],
            [
                'slug' => 'football-matchday',
                'name' => 'Football matchday',
                'description' => "Match action, touchline reactions and team portraits.\n3 hours of coverage\nMatch action\nTouchline reactions\nTeam portraits",
                'price_from' => 1800,
                'duration' => '3 hours',
                'active' => false,
                'sort' => 4,
            ],
        ];

        foreach ($services as $service) {
            $model = Service::updateOrCreate(['slug' => $service['slug']], $service);

            if ($model->packages()->exists()) {
                continue;
            }

            foreach (DefaultServicePackages::for($service['slug']) as $sort => $package) {
                $created = $model->packages()->create([
                    'name' => $package['name'],
                    'sort' => $sort,
                ]);

                foreach (array_values($package['items']) as $itemSort => $item) {
                    $created->items()->create([
                        'label' => $item['label'],
                        'value' => $item['value'],
                        'sort' => $itemSort,
                    ]);
                }
            }
        }
    }
}
