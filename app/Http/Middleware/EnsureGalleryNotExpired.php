<?php

namespace App\Http\Middleware;

use App\Enums\GalleryStatus;
use App\Models\ClientGallery;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGalleryNotExpired
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $gallery = $request->route('gallery');

        if (! $gallery instanceof ClientGallery) {
            abort(404);
        }

        if ($gallery->status === GalleryStatus::Expired || ($gallery->expires_at && $gallery->expires_at->isPast())) {
            abort(410, 'This gallery has expired.');
        }

        return $next($request);
    }
}
