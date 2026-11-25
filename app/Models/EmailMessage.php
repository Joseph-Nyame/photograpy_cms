<?php

namespace App\Models;

use App\Enums\EmailMessageStatus;
use App\Models\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailMessage extends Model
{
    use HasFactory;
    use HasUuidRouteKey;

    protected $fillable = [
        'client_id',
        'subject',
        'body',
        'sent_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'status' => EmailMessageStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
