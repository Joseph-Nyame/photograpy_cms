<?php

namespace App\Models;

use App\Models\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;
    use HasUuidRouteKey;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_from',
        'duration',
        'active',
        'sort',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'price_from' => 'decimal:2',
            'active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ServicePackage::class)->orderBy('sort');
    }
}
