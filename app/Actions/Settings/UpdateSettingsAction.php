<?php

namespace App\Actions\Settings;

use App\DTOs\Settings\UpdateSettingsDTO;
use App\Services\SettingsService;
use App\Support\Result;
use Illuminate\Support\Facades\Crypt;

class UpdateSettingsAction
{
    public function __construct(private readonly SettingsService $settings) {}

    public function execute(UpdateSettingsDTO $dto): Result
    {
        $values = [
            'studio.name' => $dto->name,
            'studio.email' => $dto->email,
            'studio.phone' => $dto->phone,
            'studio.whatsapp' => $dto->whatsapp,
            'studio.address' => $dto->address,
            'studio.hours' => $dto->hours,
            'studio.instagram' => $dto->instagram,
            'studio.map' => $dto->map,

            'seo.title' => $dto->seoTitle,
            'seo.description' => $dto->seoDescription,

            'booking.max_bookings_per_day' => $dto->maxBookings,
            'booking.buffer_minutes' => $dto->bufferMinutes,
            'booking.open_time' => $dto->open,
            'booking.close_time' => $dto->close,
            'booking.gallery_expiry_days' => $dto->expiry,
            'booking.deposit_required' => $dto->deposit ? '1' : '0',
            'booking.deposit_rate' => $dto->depositRate,
            'booking.auto_confirm_returning' => $dto->autoConfirm ? '1' : '0',

            'notifications.email' => $dto->notifyEmail,
            'notifications.booking' => $dto->notifyBooking ? '1' : '0',
            'notifications.contact' => $dto->notifyContact ? '1' : '0',
            'notifications.download' => $dto->notifyDownload ? '1' : '0',

            'publishing.domain' => $dto->domain,
            'publishing.analytics_id' => $dto->analytics,

            'email.provider' => $dto->mailProvider,

            'email.ahasend_account_id' => $dto->ahasendAccountId,
            'email.ahasend_from' => $dto->ahasendFrom,
            'email.ahasend_active' => $dto->ahasendActive ? '1' : '0',
        ];

        if (filled($dto->ahasendApiKey)) {
            $values['email.ahasend_api_key_encrypted'] = Crypt::encryptString($dto->ahasendApiKey);
            $values['email.ahasend_api_key_hint'] = SettingsService::maskApiKey($dto->ahasendApiKey);
        }

        $this->settings->store($values);

        return Result::ok($this->settings->form(), 'Settings saved.');
    }
}
