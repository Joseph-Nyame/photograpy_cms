<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Models\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;
    use HasUuidRouteKey;

    protected $fillable = [
        'subject',
        'body',
        'status',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }
}
