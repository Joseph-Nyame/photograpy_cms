<?php

namespace App\Actions\Messages;

use App\Models\ContactMessage;
use App\Support\Result;

class MarkAllMessagesReadAction
{
    public function execute(): Result
    {
        $updated = ContactMessage::query()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Result::ok(['updated' => $updated], 'All messages marked as read.');
    }
}
