<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SettingsService
{
    private const CACHE_KEY = 'settings.all';

    /**
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return Setting::query()
                ->pluck('value', 'key')
                ->all();
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function public(): array
    {
        $settings = $this->all();

        $studio = [
            'name' => $settings['studio.name'] ?? 'Lumen Studio',
            'email' => $settings['studio.email'] ?? 'hello@lumen.studio',
            'phone' => $settings['studio.phone'] ?? '',
            'whatsapp' => $settings['studio.whatsapp'] ?? '',
            'instagram' => $settings['studio.instagram'] ?? '',
            'location' => $settings['studio.location'] ?? 'Accra, Ghana',
            'address' => $settings['studio.address'] ?? '',
            'hours' => $settings['studio.hours'] ?? '',
            'map' => $settings['studio.map'] ?? '',
        ];

        return [
            'studio' => $studio,
            'socials' => $this->socials($studio),
            'seo' => [
                'title' => $settings['seo.title'] ?? 'Lumen Studio · Photography in Accra',
                'description' => $settings['seo.description'] ?? 'Weddings, events, football, birthdays and portraits photographed in Accra and beyond.',
            ],
            'booking' => [
                'max_bookings_per_day' => (int) ($settings['booking.max_bookings_per_day'] ?? 2),
                'buffer_minutes' => (int) ($settings['booking.buffer_minutes'] ?? 30),
                'open_time' => $settings['booking.open_time'] ?? '08:00',
                'close_time' => $settings['booking.close_time'] ?? '18:00',
                'gallery_expiry_days' => (int) ($settings['booking.gallery_expiry_days'] ?? 30),
                'deposit_required' => $this->toBoolean($settings['booking.deposit_required'] ?? true),
                'deposit_rate' => (float) ($settings['booking.deposit_rate'] ?? 30),
                'auto_confirm_returning' => $this->toBoolean($settings['booking.auto_confirm_returning'] ?? false),
            ],
        ];
    }

    /**
     * @return array{provider: string, apiKey: string|null, hint: string|null, accountId: string|null, from: string|null, fromName: string, active: bool, configured: bool}
     */
    public function emailConfig(): array
    {
        $s = $this->all();
        $encrypted = $s['email.ahasend_api_key_encrypted'] ?? null;
        $apiKey = null;

        if ($encrypted) {
            try {
                $apiKey = Crypt::decryptString($encrypted);
            } catch (\Throwable) {
                $apiKey = null;
            }
        }

        $studioName = $s['studio.name'] ?? config('app.name', 'Lumen Studio');

        return [
            'provider' => $s['email.provider'] ?? 'smtp',
            'apiKey' => $apiKey,
            'hint' => $s['email.ahasend_api_key_hint'] ?? null,
            'accountId' => $s['email.ahasend_account_id'] ?? null,
            'from' => $s['email.ahasend_from'] ?? ($s['studio.email'] ?? config('mail.from.address')),
            'fromName' => $studioName,
            'active' => $this->toBoolean($s['email.ahasend_active'] ?? false),
            'configured' => filled($apiKey) && filled($s['email.ahasend_account_id'] ?? null),
        ];
    }

    public static function maskApiKey(string $key): string
    {
        if (strlen($key) <= 8) {
            return str_repeat('*', strlen($key));
        }

        return substr($key, 0, 4).'****'.substr($key, -4);
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function store(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value === null ? null : (string) $value],
            );
        }

        $this->forget();
    }

    /**
     * Form-shaped settings for the admin screen.
     *
     * @return array<string, mixed>
     */
    public function form(): array
    {
        $s = $this->all();

        return [
            'name' => $s['studio.name'] ?? '',
            'email' => $s['studio.email'] ?? '',
            'phone' => $s['studio.phone'] ?? '',
            'whatsapp' => $s['studio.whatsapp'] ?? '',
            'address' => $s['studio.address'] ?? '',
            'hours' => $s['studio.hours'] ?? '',
            'instagram' => $s['studio.instagram'] ?? '',
            'map' => $s['studio.map'] ?? '',

            'maxBookings' => (int) ($s['booking.max_bookings_per_day'] ?? 2),
            'bufferMinutes' => (int) ($s['booking.buffer_minutes'] ?? 30),
            'open' => $s['booking.open_time'] ?? '08:00',
            'close' => $s['booking.close_time'] ?? '18:00',
            'expiry' => (int) ($s['booking.gallery_expiry_days'] ?? 30),
            'deposit' => $this->toBoolean($s['booking.deposit_required'] ?? '1'),
            'depositRate' => (float) ($s['booking.deposit_rate'] ?? 30),
            'autoConfirm' => $this->toBoolean($s['booking.auto_confirm_returning'] ?? '0'),

            'notifyEmail' => $s['notifications.email'] ?? '',
            'notifyBooking' => $this->toBoolean($s['notifications.booking'] ?? '1'),
            'notifyContact' => $this->toBoolean($s['notifications.contact'] ?? '1'),
            'notifyDownload' => $this->toBoolean($s['notifications.download'] ?? '0'),

            'seoTitle' => $s['seo.title'] ?? '',
            'seoDescription' => $s['seo.description'] ?? '',
            'domain' => $s['publishing.domain'] ?? '',
            'analytics' => $s['publishing.analytics_id'] ?? '',

            'mailProvider' => $s['email.provider'] ?? 'smtp',

            'ahasendApiKey' => null,
            'ahasendApiKeyHint' => $this->emailConfig()['hint'],
            'ahasendAccountId' => $s['email.ahasend_account_id'] ?? '',
            'ahasendFrom' => $s['email.ahasend_from'] ?? '',
            'ahasendActive' => $this->toBoolean($s['email.ahasend_active'] ?? false),
        ];
    }

    /**
     * @param  array<string, string>  $studio
     * @return array<int, array{label: string, href: string}>
     */
    private function socials(array $studio): array
    {
        return array_values(array_filter([
            $studio['instagram']
                ? ['label' => 'Instagram', 'href' => $studio['instagram']]
                : null,
            $studio['whatsapp']
                ? ['label' => 'WhatsApp', 'href' => $studio['whatsapp']]
                : null,
            $studio['email']
                ? ['label' => 'Email', 'href' => 'mailto:'.$studio['email']]
                : null,
        ]));
    }

    private function toBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
