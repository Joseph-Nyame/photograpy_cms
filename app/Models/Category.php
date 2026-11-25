<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'cover_image_id',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(PortfolioImage::class, 'cover_image_id');
    }

    public function portfolioImages(): HasMany
    {
        return $this->hasMany(PortfolioImage::class);
    }
}
