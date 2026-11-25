<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'studio.name' => 'Lumen Studio',
            'studio.email' => 'hello@lumen.studio',
            'studio.phone' => '+233 20 000 0000',
            'studio.whatsapp' => 'https://wa.me/233200000000',
            'studio.instagram' => 'https://instagram.com/',
            'studio.location' => 'Accra, Ghana',
            'studio.address' => '14 Boundary Road, East Legon, Accra',
            'studio.hours' => 'Mon–Fri · 9:00–18:00 · By appointment',
            'studio.map' => 'https://www.google.com/maps/search/?api=1&query=East+Legon+Accra+Ghana',

            'seo.title' => 'Lumen Studio · Photography in Accra',
            'seo.description' => 'Weddings, events, football, birthdays and portraits photographed in Accra and beyond.',

            'booking.max_bookings_per_day' => '2',
            'booking.buffer_minutes' => '30',
            'booking.open_time' => '08:00',
            'booking.close_time' => '18:00',
            'booking.gallery_expiry_days' => '30',
            'booking.deposit_required' => '1',
            'booking.deposit_rate' => '30',
            'booking.auto_confirm_returning' => '0',

            'notifications.email' => 'hello@lumen.studio',
            'notifications.booking' => '1',
            'notifications.contact' => '1',
            'notifications.download' => '0',

            'publishing.domain' => 'https://lumen.studio',
            'publishing.analytics_id' => '',

            'email.provider' => 'smtp',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        app(SettingsService::class)->forget();
    }
}
