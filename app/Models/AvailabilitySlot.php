<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilitySlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'start_time',
        'end_time',
        'active',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'active' => 'boolean',
            'sort' => 'integer',
        ];
    }
}
