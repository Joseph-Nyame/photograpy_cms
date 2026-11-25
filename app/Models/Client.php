<?php

namespace App\Models;

use App\Enums\ClientSource;
use App\Models\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;
    use HasUuidRouteKey;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'notes',
        'source',
        'subscribed',
    ];

    protected function casts(): array
    {
        return [
            'source' => ClientSource::class,
            'subscribed' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function clientGalleries(): HasMany
    {
        return $this->hasMany(ClientGallery::class);
    }

    public function emailMessages(): HasMany
    {
        return $this->hasMany(EmailMessage::class);
    }

    public function campaignRecipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }
}
