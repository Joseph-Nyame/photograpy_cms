<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\GalleryStatus;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\ClientGallery;
use App\Models\ContactMessage;

class AdminDashboardService
{
    public function __construct(
        private readonly AdminBookingService $bookings,
        private readonly BookingAvailabilityService $calendar,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function stats(): array
    {
        return [
            [
                'label' => 'Pending bookings',
                'value' => Booking::query()->where('status', BookingStatus::Pending)->count(),
                'tone' => 'sun',
                'sub' => 'Awaiting a reply',
            ],
            [
                'label' => 'Confirmed this month',
                'value' => Booking::query()
                    ->where('status', BookingStatus::Confirmed)
                    ->whereBetween('event_date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
                'tone' => 'green',
                'sub' => now()->format('F'),
            ],
            [
                'label' => 'Unread messages',
                'value' => ContactMessage::query()->whereNull('read_at')->count(),
                'tone' => 'pink',
                'sub' => 'From the contact form',
            ],
            [
                'label' => 'Active galleries',
                'value' => ClientGallery::query()->where('status', GalleryStatus::Active)->count(),
                'tone' => 'blue',
                'sub' => 'Live delivery links',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function upcomingBookings(int $limit = 5): array
    {
        return Booking::query()
            ->with(['client', 'service'])
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed])
            ->where('event_date', '>=', now()->startOfDay())
            ->orderBy('event_date')
            ->limit($limit)
            ->get()
            ->map(fn (Booking $booking): array => $this->bookings->present($booking))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recentGalleries(int $limit = 4): array
    {
        return ClientGallery::query()
            ->with('client')
            ->withCount('images')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (ClientGallery $gallery): array => [
                'title' => $gallery->title,
                'token' => $gallery->token,
                'client' => $gallery->client?->name ?? 'Unknown',
                'images' => $gallery->images_count,
                'expires' => $gallery->expires_at?->format('d M Y') ?? '—',
                'status' => $this->galleryStatus($gallery),
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, href: string, count: int}>
     */
    public function attention(): array
    {
        return [
            [
                'label' => 'Pending booking requests',
                'href' => '/admin/bookings',
                'count' => Booking::query()->where('status', BookingStatus::Pending)->count(),
            ],
            [
                'label' => 'Unread contact messages',
                'href' => '/admin/messages',
                'count' => ContactMessage::query()->whereNull('read_at')->count(),
            ],
            [
                'label' => 'Galleries expiring this week',
                'href' => '/admin/galleries',
                'count' => ClientGallery::query()
                    ->where('status', GalleryStatus::Active)
                    ->whereBetween('expires_at', [now(), now()->addWeek()])
                    ->count(),
            ],
            [
                'label' => 'Blocked dates this month',
                'href' => '/admin/calendar',
                'count' => BlockedDate::query()
                    ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
            ],
        ];
    }

    /**
     * @return array<int, array{text: string, time: string}>
     */
    public function activity(int $limit = 6): array
    {
        $bookings = Booking::query()
            ->with('client')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Booking $booking): array => [
                'text' => 'Booking · '.($booking->client?->name ?? 'Unknown'),
                'time' => $booking->created_at->diffForHumans(),
                'at' => $booking->created_at,
            ]);

        $messages = ContactMessage::query()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (ContactMessage $message): array => [
                'text' => 'Message · '.$message->name,
                'time' => $message->created_at->diffForHumans(),
                'at' => $message->created_at,
            ]);

        return $bookings
            ->merge($messages)
            ->sortByDesc('at')
            ->take($limit)
            ->map(fn (array $row): array => ['text' => $row['text'], 'time' => $row['time']])
            ->values()
            ->all();
    }

    /**
     * Month grid for the mini calendar.
     *
     * @return array{label: string, year: int, month: int, prev: array{year: int, month: int}, next: array{year: int, month: int}, cells: array<int, array<string, mixed>>}
     */
    public function month(?int $year = null, ?int $month = null): array
    {
        return $this->calendar->month($year, $month);
    }

    private function galleryStatus(ClientGallery $gallery): string
    {
        if ($gallery->status === GalleryStatus::Expired || ($gallery->expires_at && $gallery->expires_at->isPast())) {
            return 'Expired';
        }

        if ($gallery->expires_at && $gallery->expires_at->lessThanOrEqualTo(now()->addWeek())) {
            return 'Expiring';
        }

        return 'Active';
    }
}
