<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\BookingActivity;
use App\Models\Service;

class AdminBookingService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function bookings(): array
    {
        return Booking::query()
            ->with(['client', 'service', 'activities.user'])
            ->orderBy('event_date')
            ->orderBy('id')
            ->get()
            ->map(fn (Booking $booking): array => $this->present($booking))
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function serviceOptions(): array
    {
        return Service::query()
            ->orderBy('sort')
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service): array => [
                'id' => $service->id,
                'name' => $service->name,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function present(Booking $booking): array
    {
        return [
            'booking_id' => $booking->id,
            'uuid' => $booking->uuid,
            'id' => 'BK-'.str_pad((string) $booking->id, 4, '0', STR_PAD_LEFT),
            'client' => $booking->client?->name ?? 'Unknown',
            'email' => $booking->client?->email ?? '',
            'phone' => $booking->client?->phone ?? '',
            'service' => $booking->service?->name ?? 'Not specified',
            'date' => $booking->event_date?->format('d M Y') ?? '—',
            'time' => $this->time($booking),
            'location' => $booking->location ?? '—',
            'status' => $booking->status->label(),
            'status_value' => $booking->status->value,
            'payment_status' => $booking->payment_status->label(),
            'payment_status_value' => $booking->payment_status->value,
            'booking_type' => $booking->booking_type->value,
            'currency' => $booking->currency,
            'budget' => $booking->budget !== null ? (float) $booking->budget : null,
            'total_cost' => $booking->total_cost !== null ? (float) $booking->total_cost : null,
            'deposit_amount' => $booking->deposit_amount !== null ? (float) $booking->deposit_amount : null,
            'note' => $booking->message ?? '',
            'history' => $booking->activities
                ->map(fn (BookingActivity $activity): array => [
                    'id' => $activity->id,
                    'action' => $activity->action->value,
                    'action_label' => $activity->action->label(),
                    'from' => $this->label($activity->from_value, $activity->action->value),
                    'to' => $this->label($activity->to_value, $activity->action->value),
                    'note' => $activity->note ?? '',
                    'actor' => $activity->user?->name ?? 'System',
                    'date' => $activity->created_at?->format('d M Y, H:i') ?? '',
                ])
                ->all(),
        ];
    }

    private function label(?string $value, string $action): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($action) {
            'status_changed' => BookingStatus::from($value)->label(),
            'payment_updated' => PaymentStatus::from($value)->label(),
            default => ucfirst(str_replace('_', ' ', $value)),
        };
    }

    private function time(Booking $booking): string
    {
        if ($booking->start_time === null) {
            return 'TBC';
        }

        $start = substr((string) $booking->start_time, 0, 5);

        return $booking->end_time !== null
            ? $start.' – '.substr((string) $booking->end_time, 0, 5)
            : $start;
    }
}
