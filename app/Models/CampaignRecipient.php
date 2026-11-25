<?php

namespace App\Models;

use App\Enums\CampaignRecipientStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'client_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CampaignRecipientStatus::class,
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
