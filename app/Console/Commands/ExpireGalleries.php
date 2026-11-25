<?php

namespace App\Console\Commands;

use App\Enums\GalleryStatus;
use App\Models\ClientGallery;
use Illuminate\Console\Command;

class ExpireGalleries extends Command
{
    protected $signature = 'galleries:expire';

    protected $description = 'Mark galleries past their expiry date as expired';

    public function handle(): int
    {
        $count = ClientGallery::query()
            ->where('status', GalleryStatus::Active)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => GalleryStatus::Expired]);

        $this->info("Expired {$count} gallery(ies).");

        return self::SUCCESS;
    }
}
