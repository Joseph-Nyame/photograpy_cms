<?php

namespace App\Models;

use App\Models\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;

class BookingLocation extends Model
{
    use HasUuidRouteKey;

    protected $fillable = [
        'name',
        'currency',
        'active',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort' => 'integer',
        ];
    }
}
