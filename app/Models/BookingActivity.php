<?php

namespace App\Models;

use App\Enums\BookingActivityAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingActivity extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'action',
        'from_value',
        'to_value',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'action' => BookingActivityAction::class,
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
