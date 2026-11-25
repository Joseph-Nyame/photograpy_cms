<?php

namespace App\Models;

use App\Enums\GalleryStatus;
use App\Models\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientGallery extends Model
{
    use HasFactory;
    use HasUuidRouteKey;

    protected $fillable = [
        'client_id',
        'title',
        'token',
        'passcode_hash',
        'expires_at',
        'allow_download',
        'status',
    ];

    protected $hidden = [
        'passcode_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'allow_download' => 'boolean',
            'status' => GalleryStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class, 'gallery_id');
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class, 'gallery_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(GalleryAccessLog::class, 'gallery_id');
    }
}
