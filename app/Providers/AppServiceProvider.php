<?php

namespace App\Providers;

use App\Models\ClientGallery;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
        URL::forceScheme('https');
        }

        RateLimiter::for('gallery-unlock', function (Request $request): Limit {
            $gallery = $request->route('gallery');
            $token = $gallery instanceof ClientGallery ? $gallery->token : (string) $gallery;

            return Limit::perMinute(5)->by($request->ip().'|'.$token);
        });
    }
}
