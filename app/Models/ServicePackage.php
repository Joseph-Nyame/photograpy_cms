<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePackage extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServicePackageItem::class)->orderBy('sort');
    }
}
