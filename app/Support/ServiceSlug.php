<?php

namespace App\Support;

use App\Models\Service;
use Illuminate\Support\Str;

class ServiceSlug
{
    public function unique(string $name): string
    {
        $base = Str::slug($name) ?: 'service';
        $slug = $base;
        $suffix = 2;

        while (Service::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
