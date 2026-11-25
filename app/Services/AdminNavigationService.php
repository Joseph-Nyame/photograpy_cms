<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\GalleryStatus;
use App\Models\Booking;
use App\Models\ClientGallery;
use App\Models\ContactMessage;

class AdminNavigationService
{
    /**
     * @return array{pendingBookings: int, unreadMessages: int, activeGalleries: int}
     */
    public function counts(): array
    {
        return [
            'pendingBookings' => Booking::query()->where('status', BookingStatus::Pending)->count(),
            'unreadMessages' => ContactMessage::query()->whereNull('read_at')->count(),
            'activeGalleries' => ClientGallery::query()->where('status', GalleryStatus::Active)->count(),
        ];
    }
}
