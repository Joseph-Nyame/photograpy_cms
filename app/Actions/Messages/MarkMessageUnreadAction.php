<?php

namespace App\Actions\Messages;

use App\Models\ContactMessage;
use App\Support\Result;

class MarkMessageUnreadAction
{
    public function execute(ContactMessage $message): Result
    {
        $message->update(['read_at' => null]);

        return Result::ok($message->refresh(), 'Marked as unread.');
    }
}
