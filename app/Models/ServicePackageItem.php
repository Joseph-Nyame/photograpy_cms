<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePackageItem extends Model
{
    protected $fillable = [
        'service_package_id',
        'label',
        'value',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'sort' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class);
    }
}
