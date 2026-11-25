<?php

namespace App\Models;

use App\Enums\GalleryAccessAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'gallery_id',
        'ip',
        'user_agent',
        'action',
    ];

    protected function casts(): array
    {
        return [
            'action' => GalleryAccessAction::class,
        ];
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(ClientGallery::class, 'gallery_id');
    }
}
