<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/settings')->assertRedirect('/login');
    }

    public function test_admin_can_view_settings(): void
    {
        Setting::create(['key' => 'studio.name', 'value' => 'Lumen Studio']);

        $this->actingAs($this->admin())
            ->get('/admin/settings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminSettings')
                ->where('settings.name', 'Lumen Studio')
                ->where('settings.maxBookings', 2)
                ->where('settings.bufferMinutes', 30)
                ->where('settings.depositRate', 30));
    }

    public function test_admin_can_update_settings(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', $this->payload())
            ->assertSessionHas('success', true);

        $settings = app(SettingsService::class)->all();

        $this->assertSame('New Studio', $settings['studio.name']);
        $this->assertSame('5', $settings['booking.max_bookings_per_day']);
        $this->assertSame('60', $settings['booking.buffer_minutes']);
        $this->assertSame('0', $settings['booking.deposit_required']);
        $this->assertSame('25', $settings['booking.deposit_rate']);
        $this->assertSame('1', $settings['booking.auto_confirm_returning']);
    }

    public function test_admin_can_save_masked_ahasend_credentials(): void
    {
        $payload = $this->payload();
        $payload['mailProvider'] = 'ahasend';
        $payload['ahasendApiKey'] = 'AGT-1234567890xyz';
        $payload['ahasendAccountId'] = 'acc-42';
        $payload['ahasendFrom'] = 'studio@lumen.studio';
        $payload['ahasendActive'] = true;

        $this->actingAs($this->admin())
            ->put('/admin/settings', $payload)
            ->assertSessionHas('success', true);

        $this->actingAs($this->admin())
            ->get('/admin/settings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminSettings')
                ->where('settings.mailProvider', 'ahasend')
                ->where('settings.ahasendApiKeyHint', 'AGT-****0xyz')
                ->where('settings.ahasendAccountId', 'acc-42')
                ->where('settings.ahasendFrom', 'studio@lumen.studio')
                ->where('settings.ahasendActive', true)
                ->missing('settings.email.ahasend_api_key_encrypted'));

        $this->assertSame('AGT-1234567890xyz', app(SettingsService::class)->emailConfig()['apiKey']);
        $this->assertFalse(Setting::query()->where('value', 'AGT-1234567890xyz')->exists());
    }

    public function test_blank_ahasend_api_key_keeps_the_existing_key(): void
    {
        $payload = $this->payload();
        $payload['ahasendApiKey'] = 'AGT-1234567890xyz';
        $payload['ahasendAccountId'] = 'acc-42';

        $this->actingAs($this->admin())->put('/admin/settings', $payload)->assertSessionHas('success', true);

        $payload['ahasendApiKey'] = '';

        $this->actingAs($this->admin())->put('/admin/settings', $payload)->assertSessionHas('success', true);

        $config = app(SettingsService::class)->emailConfig();

        $this->assertSame('AGT-1234567890xyz', $config['apiKey']);
        $this->assertSame('AGT-****0xyz', $config['hint']);
    }

    public function test_settings_update_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', [])
            ->assertSessionHasErrors(['name', 'email', 'maxBookings', 'open', 'close', 'expiry', 'depositRate']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'name' => 'New Studio',
            'email' => 'hello@new.studio',
            'phone' => '+233 20 000 0000',
            'whatsapp' => 'https://wa.me/233200000000',
            'address' => '14 Boundary Road',
            'hours' => 'Mon–Fri',
            'instagram' => 'https://instagram.com/new',
            'map' => 'https://maps.google.com',
            'maxBookings' => 5,
            'bufferMinutes' => 60,
            'open' => '09:00',
            'close' => '17:00',
            'expiry' => 45,
            'deposit' => false,
            'depositRate' => 25,
            'autoConfirm' => true,
            'notifyEmail' => 'hello@new.studio',
            'notifyBooking' => true,
            'notifyContact' => true,
            'notifyDownload' => false,
            'seoTitle' => 'New Studio',
            'seoDescription' => 'A new description.',
            'domain' => 'https://new.studio',
            'analytics' => 'G-NEW',
            'mailProvider' => 'resend',
        ];
    }
}
