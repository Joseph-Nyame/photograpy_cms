<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favourite extends Model
{
    use HasFactory;

    protected $fillable = [
        'gallery_id',
        'gallery_image_id',
    ];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(ClientGallery::class, 'gallery_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'gallery_image_id');
    }
}
