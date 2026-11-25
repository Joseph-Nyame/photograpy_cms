<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\PaymentStatus;
use App\Models\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;
    use HasUuidRouteKey;

    protected $fillable = [
        'client_id',
        'service_id',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'message',
        'status',
        'decline_reason',
        'booking_type',
        'currency',
        'budget',
        'total_cost',
        'deposit_amount',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'budget' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'status' => BookingStatus::class,
            'booking_type' => BookingType::class,
            'payment_status' => PaymentStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(BookingActivity::class)->orderBy('created_at')->orderBy('id');
    }
}
