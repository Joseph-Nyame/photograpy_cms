<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@lumen.studio')],
            [
                'name' => env('ADMIN_NAME', 'Lumen Admin'),
                'password' => env('ADMIN_PASSWORD', 'LumenStudio2026!'),
            ],
        );

        $this->call([
            SettingSeeder::class,
            BookingLocationSeeder::class,
            ServiceSeeder::class,
            CategorySeeder::class,
            PortfolioImageSeeder::class,
        ]);
    }
}
