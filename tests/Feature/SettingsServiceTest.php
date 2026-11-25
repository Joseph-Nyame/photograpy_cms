<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_settings_use_stored_values_and_defaults(): void
    {
        Setting::create(['key' => 'studio.name', 'value' => 'Test Studio']);
        Setting::create(['key' => 'studio.email', 'value' => 'studio@example.com']);
        Setting::create(['key' => 'studio.instagram', 'value' => 'https://instagram.com/test']);
        Setting::create(['key' => 'studio.whatsapp', 'value' => 'https://wa.me/233200000000']);
        Setting::updateOrCreate(['key' => 'booking.deposit_rate'], ['value' => '35']);

        $settings = app(SettingsService::class)->public();

        $this->assertSame('Test Studio', $settings['studio']['name']);
        $this->assertSame('studio@example.com', $settings['studio']['email']);
        $this->assertSame(35.0, $settings['booking']['deposit_rate']);
        $this->assertSame(2, $settings['booking']['max_bookings_per_day']);
        $this->assertSame('Instagram', $settings['socials'][0]['label']);
        $this->assertSame('mailto:studio@example.com', $settings['socials'][2]['href']);
    }

    public function test_forget_refreshes_cached_settings(): void
    {
        Setting::create(['key' => 'studio.name', 'value' => 'First Name']);

        $service = app(SettingsService::class);
        $this->assertSame('First Name', $service->public()['studio']['name']);

        Setting::query()->where('key', 'studio.name')->update(['value' => 'Second Name']);
        $service->forget();

        $this->assertSame('Second Name', $service->public()['studio']['name']);
    }
}
