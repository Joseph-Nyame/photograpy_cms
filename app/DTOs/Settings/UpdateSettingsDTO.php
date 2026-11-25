<?php

namespace App\DTOs\Settings;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class UpdateSettingsDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public ?string $whatsapp,
        public ?string $address,
        public ?string $hours,
        public ?string $instagram,
        public ?string $map,
        public int $maxBookings,
        public int $bufferMinutes,
        public string $open,
        public string $close,
        public int $expiry,
        public bool $deposit,
        public float $depositRate,
        public bool $autoConfirm,
        public ?string $notifyEmail,
        public bool $notifyBooking,
        public bool $notifyContact,
        public bool $notifyDownload,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public ?string $domain,
        public ?string $analytics,
        public ?string $mailProvider,
        public ?string $ahasendApiKey = null,
        public ?string $ahasendAccountId = null,
        public ?string $ahasendFrom = null,
        public bool $ahasendActive = false,
    ) {}

    public static function fromRequest(Request $request): static
    {
        /** @var array<string, mixed> $d */
        $d = $request->validated();

        return new self(
            name: $d['name'],
            email: $d['email'],
            phone: $d['phone'] ?? null,
            whatsapp: $d['whatsapp'] ?? null,
            address: $d['address'] ?? null,
            hours: $d['hours'] ?? null,
            instagram: $d['instagram'] ?? null,
            map: $d['map'] ?? null,
            maxBookings: (int) $d['maxBookings'],
            bufferMinutes: (int) $d['bufferMinutes'],
            open: $d['open'],
            close: $d['close'],
            expiry: (int) $d['expiry'],
            deposit: (bool) ($d['deposit'] ?? false),
            depositRate: (float) $d['depositRate'],
            autoConfirm: (bool) ($d['autoConfirm'] ?? false),
            notifyEmail: $d['notifyEmail'] ?? null,
            notifyBooking: (bool) ($d['notifyBooking'] ?? false),
            notifyContact: (bool) ($d['notifyContact'] ?? false),
            notifyDownload: (bool) ($d['notifyDownload'] ?? false),
            seoTitle: $d['seoTitle'] ?? null,
            seoDescription: $d['seoDescription'] ?? null,
            domain: $d['domain'] ?? null,
            analytics: $d['analytics'] ?? null,
            mailProvider: $d['mailProvider'] ?? null,
            ahasendApiKey: $d['ahasendApiKey'] ?? null,
            ahasendAccountId: $d['ahasendAccountId'] ?? null,
            ahasendFrom: $d['ahasendFrom'] ?? null,
            ahasendActive: (bool) ($d['ahasendActive'] ?? false),
        );
    }
}
