<?php

namespace App\Models;

use App\Models\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryImage extends Model
{
    use HasFactory;
    use HasUuidRouteKey;

    protected $fillable = [
        'gallery_id',
        'original_path',
        'preview_path',
        'thumb_path',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(ClientGallery::class, 'gallery_id');
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }
}
