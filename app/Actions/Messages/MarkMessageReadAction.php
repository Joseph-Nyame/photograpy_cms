<?php

namespace App\Actions\Messages;

use App\Models\ContactMessage;
use App\Support\Result;

class MarkMessageReadAction
{
    public function execute(ContactMessage $message): Result
    {
        if ($message->read_at === null) {
            $message->update(['read_at' => now()]);
        }

        return Result::ok($message->refresh(), 'Marked as read.');
    }
}
