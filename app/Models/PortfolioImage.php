<?php

namespace App\Models;

use App\Models\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioImage extends Model
{
    use HasFactory;
    use HasUuidRouteKey;

    protected $fillable = [
        'category_id',
        'path',
        'alt',
        'sort',
        'featured',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
